<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Award.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local\award;

use block_motrain\local\api_error;
use block_motrain\local\client_exception;
use block_motrain\local\reason\lang_reason;
use block_motrain\manager;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Award.
 *
 * Create an award for a person, and then give them the coins.
 *
 * It is expected that the user belongs to team, if not giving them
 * coins will trigger an exception. However, we will silently handle
 * the case where the player does not exist, except when strict.
 *
 * To avoid exceptions, check the result of manager::is_enabled and
 * manager::is_player or manager::can_earn_in_context prior to attempting
 * to use an award on a particular user. Additionally, if manager::is_paused
 * then awards should not be attempted.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class award {

    /** @var int The user ID. */
    protected $userid;
    /** @var int The context ID. */
    protected $contextid;
    /** @var int The action name. */
    protected $actionname;
    /** @var int The action hash, for uniqueness for user/context pair. */
    protected $actionhash;
    /** @var bool $strict Whether we should be strict. */
    protected $strict = false;

    /** @var bool $hasretried Whether we have automatically retried following an error. */
    protected $hasretried = false;

    /**
     * Constructor.
     *
     * @param int $userid The user ID.
     * @param int $contextid The context ID.
     * @param int $actionname The action name.
     * @param int $actionhash The action hash .
     */
    public function __construct($userid, $contextid, $actionname = 'unknown', $actionhash = '') {
        $this->userid = $userid;
        $this->contextid = $contextid;
        $this->actionname = $actionname;
        $this->actionhash = $actionhash;
    }

    /**
     * Award the coins.
     *
     * @param int $coins The number of coins.
     * @param lang_reason|null $reason The reason.
     */
    protected function award_coins($coins, lang_reason $reason = null) {
        $manager = manager::instance();
        $manager->require_enabled();
        $manager->require_not_paused();

        $coins = (int) $coins;
        $userid = $this->userid;

        if ($coins <= 0) {
            throw new moodle_exception('invalidcoinamount', 'block_motrain');
        }

        // Resolve the team.
        $teamid = $manager->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid) {
            throw new \moodle_exception('userteamnotfound', 'block_motrain');
        }

        // Resolve the player.
        $playerid = $manager->get_player_mapper()->get_player_id($userid, $teamid);
        if (!$playerid) {
            throw new \moodle_exception('playeridnotfound', 'block_motrain');
        }

        // Send the coins, and invalidate the cash.
        try {
            $manager->get_client()->add_coins($playerid, $coins, $reason);
        } catch (api_error $e) {

            // It would appear that the player is not found. That is likely because we have a mapping that
            // is no tied to a player that has been deleted. In this case, we delete the mapping and retry.
            if ($e->get_http_code() == 404 && $playerid && !$this->hasretried) {
                $this->hasretried = true;
                $manager->get_player_mapper()->remove_user($userid);
                $this->award_coins($coins, $reason);
                return;
            }

            throw $e;
        }
        $manager->get_balance_proxy()->invalidate_balance($userid);
    }

    /**
     * Whether this was recorded previously.
     *
     * Note that this does not care whether the coins were not broadcasted.
     * The idea is that events that were not broadcasted should eventually
     * be broadcasted. So we recorded them, and consider them as recorded.
     *
     * @return bool
     */
    public function has_been_recorded_previously() {
        global $DB;
        $params = [
            'userid' => $this->userid,
            'contextid' => $this->contextid,
            'actionname' => $this->actionname,
            'actionhash' => $this->actionhash
        ];
        return $DB->record_exists('block_motrain_log', $params);
    }

    /**
     * Give x amount of coins for this award.
     *
     * When everything seems fine with regards to the user, the coins
     * and the state of the system, we will always record a log with
     * the coins as we assume that they should have been awarded. Even
     * when we could not successfully send the coins to the server. Although
     * in the later case, we will return false and we will not trigger
     * the event coins_awarded. It is assumed that we may reattempt to
     * broadcast the coins at a later stage.
     *
     * However, when there is a problem with the award, such as the amount
     * of coins invalid, or we could not confirm that the user is actually
     * a player (e.g. their team is unknown) then we will trigger an
     * exception and will not record anything. Note that not identifying
     * the player ID is not a blocking issue because this could be due to
     * the inability to recover/create the player remotely, the user is
     * still considered a player, and as such should retrieve their coins.
     *
     * In strict mode nothing is recorded to the logs upon any failure.
     *
     * @param int $coins The number of coins.
     * @param lang_reason|null $reason The reason.
     * @return bool True when successfully awarded.
     */
    public function give($coins, lang_reason $reason = null) {
        global $DB;

        $broadcasted = time();
        $broadcasterror = null;
        $rethrow = null;

        // In strict mode we do not catch any exception.
        if ($this->strict) {
            $this->award_coins($coins, $reason);

        } else {
            try {
                $this->award_coins($coins, $reason);
            } catch (api_error $e) {
                $broadcasted = 0;
                $broadcasterror = 'HTTP ' . $e->get_http_code() . ' ' . $e->get_error_code();
            } catch (client_exception $e) {
                $broadcasted = 0;
                $broadcasterror = 'HTTP ' . $e->get_http_code() . ' ' . $e->getMessage();
            } catch (moodle_exception $e) {
                $broadcasted = 0;
                $broadcasterror = $e->errorcode;
                // We passively handle the player ID not found, the rest should not happen.
                if ($e->errorcode !== 'playeridnotfound') {
                    $rethrow = $e;
                }
            }
        }

        $DB->insert_record('block_motrain_log', array_merge([
            'userid' => $this->userid,
            'contextid' => $this->contextid,
            'actionname' => $this->actionname,
            'actionhash' => $this->actionhash,
            'coins' => $coins,
            'timecreated' => time(),
            'timebroadcasted' => $broadcasted,
            'broadcasterror' => $broadcasterror
        ]));

        if ($rethrow) {
            throw $e;
        }

        $success = !empty($broadcasted);
        if ($success) {
            $event = \block_motrain\event\coins_earned::create([
                'contextid' => $this->contextid,
                'relateduserid' => $this->userid,
                'other' => [
                    'amount' => $coins,
                ]
            ]);
            $event->trigger();
        }

        return $success;
    }

    /**
     * Set the strictness.
     *
     * @param bool $strictness Whether strict or not.
     */
    public function set_strict($strictness) {
        $this->strict = (bool) $strictness;
    }

}

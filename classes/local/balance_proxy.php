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
 * Balance proxy.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use block_motrain\manager;
use cache;

defined('MOODLE_INTERNAL') || die();

/**
 * Balance proxy.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class balance_proxy {

    /** @var cache The cache. */
    protected $coinscache;
    /** @var manager The manager. */
    protected $manager;

    /**
     * Constructor.
     *
     * @param manager The manager.
     */
    public function __construct(manager $manager) {
        $this->manager = $manager;
        $this->coinscache = cache::make('block_motrain', 'coins');
    }

    /**
     * Get Brava allowance.
     *
     * @param object|int The user, or its ID.
     * @return object
     */
    public function get_brava_allowance($userorid) {
        return $this->get_player_coins_cached($userorid)->brava_allowance;
    }

    /**
     * Get Brava given.
     *
     * @param object|int The user, or its ID.
     * @return object
     */
    public function get_brava_given($userorid) {
        return $this->get_player_coins_cached($userorid)->brava_given;
    }

    /**
     * Get the balance.
     *
     * @param object|int The user, or its ID.
     * @return int
     */
    public function get_balance($userorid) {
        return $this->get_player_coins_cached($userorid)->coins;
    }

    /**
     * Get the tickets.
     *
     * @param object|int The user, or its ID.
     * @return int
     */
    public function get_tickets($userorid) {
        return $this->get_player_coins_cached($userorid)->tickets;
    }

    /**
     * Get the total earned.
     *
     * @param object|int The user, or its ID.
     * @return int
     */
    public function get_total_earned($userorid) {
        return $this->get_player_coins_cached($userorid)->coins_earned_lifetime;
    }

    /**
     * Get the coins from server.
     *
     * @param int|object $userorid The user, or its ID.
     * @param bool $hasretried Whether we have retried.
     * @return object With coins, coins_earned_lifetime, and tickets.
     */
    protected function get_player_balance($userorid, $hasretried = false) {
        $default = (object) [
            'coins' => 0,
            'coins_earned_lifetime' => 0,
            'tickets' => 0,
            'brava_given' => 0,
            'brava_remaining' => 0,
            'brava_allowance' => 0
        ];
        $manager = $this->manager;

        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }

        if (!$manager->is_enabled() || $manager->is_paused()) {
            return $default;
        }

        $teamid = $manager->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid) {
            return $default;
        }

        $playerid = $manager->get_player_mapper()->get_player_id($userorid, $teamid);
        if (!$playerid) {
            return $default;
        }

        try {
            $player = $manager->get_client()->get_player($playerid);
            return (object) [
                'coins' => (int) ($player->coins ?? 0),
                'coins_earned_lifetime' => (int) ($player->coins_earned_lifetime ?? 0),
                'tickets' => (int) ($player->tickets ?? 0),
                'brava_given' => (int) ($player->brava->given ?? 0),
                'brava_remaining' => (int) ($player->brava->remaining ?? 0),
                'brava_allowance' => (int) ($player->brava->allowance ?? 0),
            ];
        } catch (api_error $e) {
            if ($e->get_http_code() == 404 && $playerid && !$hasretried) {
                $manager->get_player_mapper()->remove_user($userid);
                return $this->get_player_balance($userid, true);
            }
            return $default;
        } catch (client_exception $e) {
            return $default;
        }
    }

    /**
     * Get the coins from server.
     *
     * @param int|object $userorid The user, or its ID.
     * @return object With coins, and coins_earned_lifetime.
     */
    protected function get_player_coins_cached($userorid) {
        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }
        $userid = (int) $userid;

        if (($data = $this->coinscache->get($userid)) === false) {
            $data = $this->get_player_balance($userorid);
            $this->coinscache->set($userid, $data);
        }

        return $data;
    }

    /**
     * Invalidate the balance for all users.
     */
    public function invalidate_all() {
        if (method_exists($this->coinscache, 'purge')) {
            $this->coinscache->purge();
        }
    }

    /**
     * Invalidate the balance for user.
     *
     * @param int $userid The user ID.
     */
    public function invalidate_balance($userid) {
        return $this->coinscache->delete((int) $userid);
    }

    /**
     * Set the balance cache.
     *
     * @param int $userid The user ID.
     * @param int $coins The coins.
     * @deprecated Do not use.
     */
    public function set_balance($userid, $coins) {
        throw new \coding_exception('Deprecated function.');
    }

}

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
 * Player mapper.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use core_user;

defined('MOODLE_INTERNAL') || die();

/**
 * Player mapper.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class player_mapper {

    /** @var string The account ID. */
    protected $accountid;
    /** @var client The client. */
    protected $client;

    /**
     * Constructor.
     */
    public function __construct($client, $accountid) {
        $this->client = $client;
        $this->accountid = $accountid;
    }

    /**
     * Get the player ID of a user.
     *
     * @param int|object $userorid The user or its ID.
     * @param string $teamid The team ID, if need to be created.
     */
    public function get_player_id($userorid, $teamid) {
        global $DB;

        $user = null;
        $userid = $userorid;
        if (is_object($userid)) {
            $user = $userid;
            $userid = $user->id;
        }

        $mapping = $DB->get_record('block_motrain_playermap', ['accountid' => $this->accountid, 'userid' => $userid]);
        if (empty($mapping) || (empty($mapping->playerid) && !$mapping->blocked)) {
            $user = $user ? $user : $this->get_user($userid);
            $playerid = null;
            $blockedreason = null;

            // Attempt to resolve user on dashboard.
            $player = $this->client->get_player_by_email($teamid, $user->email);
            if ($player) {
                $playerid = $player->id;
            }

            // Attempt to create the user on the dashboard.
            if (empty($playerid)) {
                try {
                    $player = $this->client->create_player($teamid, [
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'email' => $user->email,
                    ]);
                    $playerid = $player->id;
                } catch (api_error $e) {
                    $playerid = null;
                    $blockedreason = $e->get_error_code();
                } catch (client_exception $e) {
                    $playerid = null;
                    $blockedreason = $e->errorcode;
                }
            }

            if (empty($mapping)) {
                $mapping = (object) [
                    'accountid' => $this->accountid,
                    'userid' => $userid
                ];
            }

            $mapping->playerid = $playerid;
            $mapping->blocked = !empty($blockedreason);
            $mapping->blockedreason = $blockedreason;
            if (!empty($mapping->id)) {
                $DB->update_record('block_motrain_playermap', $mapping);
            } else {
                $mapping->id = $DB->insert_record('block_motrain_playermap', $mapping);
            }
        }

        return $mapping ? $mapping->playerid : null;
    }

    /**
     * Get the user object.
     *
     * @param int $userid The user ID.
     * @return object The user.
     */
    protected function get_user($userid) {
        global $USER;

        $user = $USER;
        if ($USER->id != $userid) {
            $user = core_user::get_user($userid, '*');
        }

        return $user;
    }

    /**
     * Remove the mapping for user.
     *
     * @param int $userid The user ID.
     */
    public function remove_user($userid) {
        global $DB;
        $DB->delete_records('block_motrain_playermap', ['accountid' => $this->accountid, 'userid' => $userid]);
    }

    /**
     * Unblock the user.
     *
     * @param int $userid The user ID.
     */
    public function unblock_user($userid) {
        global $DB;
        $DB->set_field('block_motrain_playermap', 'blocked', 0, ['accountid' => $this->accountid, 'userid' => $userid]);
    }

}
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
    /** @var bool Whether the mapping only considers local data. */
    protected $localonly = false;
    /** @var bool Whether we support synchronising metadata. */
    protected $syncmetadata = false;
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
        if ((empty($mapping) || (empty($mapping->playerid) && !$mapping->blocked)) && !$this->localonly) {
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
                    'userid' => $userid,
                ];
            }

            $mapping->playerid = $playerid;
            $mapping->metadatahash = $this->get_user_metadata_hash($user);
            $mapping->metadatastale = 0;
            $mapping->blocked = !empty($blockedreason);
            $mapping->blockedreason = $blockedreason;
            if (!empty($mapping->id)) {
                $DB->update_record('block_motrain_playermap', $mapping);
            } else {
                $mapping->id = $DB->insert_record('block_motrain_playermap', $mapping);
            }
        }

        // If the player metadata is stale, update the player.
        if (!empty($mapping) && !empty($mapping->metadatastale) && $this->syncmetadata) {
            $user = $user ? $user : $this->get_user($userid);

            // In some instances, the user metadata can be marked as stale, but the user object
            // we are dealing with seems to be outdated (points to the previous metadata hash).
            // This can happen when an admin modifies a user that is already logged in, and that user
            // is entering this code with an outdated object. To remedy this, we force the user object
            // to be loaded from the database to guarantee that we've got the latest one.
            if ($mapping->metadatahash === $this->get_user_metadata_hash($user)) {
                $user = $this->get_user($userid, true);
            }

            try {
                $this->client->update_player($mapping->playerid, [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                ]);
            } catch (client_exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
            }

            $mapping->metadatastale = 0;
            $mapping->metadatahash = $this->get_user_metadata_hash($user);
            $DB->update_record('block_motrain_playermap', $mapping);
        }

        return $mapping ? $mapping->playerid : null;
    }

    /**
     * Get the user object.
     *
     * @param int $userid The user ID.
     * @param bool $forcedb Force loading from database.
     * @return object The user.
     */
    protected function get_user($userid, $forcedb = false) {
        global $USER;

        $user = $USER;
        if ($forcedb || $USER->id != $userid) {
            $user = core_user::get_user($userid, '*');
        }

        return $user;
    }

    /**
     * Get the user's metadata hash.
     *
     * @param object $user The user.
     */
    protected function get_user_metadata_hash($user) {
        return sha1($user->firstname . '|' . $user->lastname . '|' . $user->email);
    }

    /**
     * Whether the mapping only considers local data.
     *
     * @param bool $localonly Local only.
     */
    public function set_local_only($localonly) {
        $this->localonly = (bool) $localonly;
    }

    /**
     * Whether we supports updating the metadata of players.
     *
     * @param bool $syncmetadata Enabled when true.
     */
    public function set_sync_metadata($syncmetadata) {
        $this->syncmetadata = (bool) $syncmetadata;
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

    /**
     * Check a mapping's metadata staleness.
     *
     * This checks whether the user's metadata has been modified since the last time
     * we processed it. If yes, it will mark the mapping as stale to trigger the
     * update on the remote server if that's the case.
     *
     * This only affects existing mappings.
     *
     * @param object $user The user.
     */
    public function update_mapping_metadata_staleness($user) {
        global $DB;

        if (!$this->syncmetadata) {
            return;
        }


        $metadatahash = $this->get_user_metadata_hash($user);
        $DB->set_field_select(
            'block_motrain_playermap',
            'metadatastale',
            1,
            'accountid = ? AND userid = ? AND (metadatahash != ? OR metadatahash IS NULL)',
            [
                $this->accountid,
                $user->id,
                $metadatahash,
            ]
        );
    }

}

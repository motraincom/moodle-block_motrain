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
    /** @var bool Whether we accept remote players from foreign teams. */
    protected $acceptforeignteams = true;
    /** @var bool Whether we can change a person's team. */
    protected $allowteamchange = true;
    /** @var bool Whether the mapping only considers local data. */
    protected $localonly = false;
    /** @var bool Whether we support synchronising metadata. */
    protected $syncmetadata = false;
    /** @var \block_motrain\client The client. */
    protected $client;

    /**
     * Constructor.
     */
    public function __construct($client, $accountid) {
        $this->client = $client;
        $this->accountid = $accountid;
    }

    /**
     * Get the local user ID.
     *
     * @param string $remoteplayerid The remote player ID.
     * @return int|null
     */
    public function get_local_user_id($remoteplayerid) {
        global $DB;
        $mapping = $DB->get_record('block_motrain_playermap', ['accountid' => $this->accountid, 'playerid' => $remoteplayerid]);
        return $mapping ? $mapping->userid : null;
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

        $mapping = $this->get_player_mapping($userid);
        if ((empty($mapping) || (empty($mapping->playerid) && !$mapping->blocked)) && !$this->localonly) {
            $user = $user ? $user : $this->get_user($userid);
            $playerid = null;
            $blockedreason = null;
            $actualteamid = null;

            // Attempt to resolve user on dashboard.
            if ($this->acceptforeignteams) {
                $player = $this->client->get_player_by_email_in_account($user->email);
            } else {
                $player = $this->client->get_player_by_email($teamid, $user->email);
            }
            if ($player) {
                $playerid = $player->id;
                $actualteamid = $player->team_id;
            }

            // Prepare to maybe creating the user.
            $hasbeencreated = false;
            $metadatahash = null;

            // Attempt to create the user on the dashboard.
            if (empty($playerid)) {
                [$playermetadata, $metadatahash] = $this->get_user_metadata($user);
                try {
                    $player = $this->client->create_player($teamid, $playermetadata);
                    $playerid = $player->id;
                    $actualteamid = $player->team_id;
                    $hasbeencreated = true;
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
            $mapping->teamid = $actualteamid;
            $mapping->metadatahash = $metadatahash;
            $mapping->metadatastale = $hasbeencreated ? 0 : 1;
            $mapping->blocked = !empty($blockedreason);
            $mapping->blockedreason = $blockedreason;
            if (!empty($mapping->id)) {
                $DB->update_record('block_motrain_playermap', $mapping);
            } else {
                $mapping->id = $DB->insert_record('block_motrain_playermap', $mapping);
            }
        }

        // Add the teamid to older records created before teamid was added, and where it is undefined.
        if (!empty($mapping) && $mapping->playerid && !$mapping->teamid) {
            $mapping->teamid = $teamid;
            $DB->update_record('block_motrain_playermap', $mapping);
        }

        // If there is a discrepancy between the mapped team, and the team we expect, we update it.
        if (!empty($mapping) && $mapping->playerid && $mapping->teamid !== $teamid
                && $this->allowteamchange && !$mapping->blocked && !$this->localonly) {

            $blockedreason = null;
            try {
                $this->client->set_player_team($mapping->playerid, $teamid);
            } catch (api_error $e) {
                $blockedreason = $e->get_error_code();
            } catch (client_exception $e) {
                $blockedreason = $e->errorcode;
            }

            if (!$blockedreason) {
                $mapping->teamid = $teamid;
            }
            $mapping->blocked = !empty($blockedreason);
            $mapping->blockedreason = $blockedreason;
            $DB->update_record('block_motrain_playermap', $mapping);
        }

        // If the player metadata is stale, update the player.
        if (!empty($mapping) && $mapping->playerid && !empty($mapping->metadatastale)
                && $this->syncmetadata && !$this->localonly) {
            $user = $user ? $user : $this->get_user($userid);
            [$playermetadata, $metadatahash] = $this->get_user_metadata($user);

            // In some instances, the user metadata can be marked as stale, but the user object
            // we are dealing with seems to be outdated (points to the previous metadata hash).
            // This can happen when an admin modifies a user that is already logged in, and that user
            // is entering this code with an outdated object. To remedy this, we force the user object
            // to be loaded from the database to guarantee that we've got the latest one.
            if ($mapping->metadatahash === $metadatahash) {
                $user = $this->get_user($userid, true);
                [$playermetadata, $metadatahash] = $this->get_user_metadata($user);
            }

            try {
                $this->client->update_player($mapping->playerid, $playermetadata);
            } catch (client_exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
            }

            $mapping->metadatastale = 0;
            $mapping->metadatahash = $metadatahash;
            $DB->update_record('block_motrain_playermap', $mapping);
        }

        return $mapping ? $mapping->playerid : null;
    }

    /**
     * Get player mapping.
     *
     * @param int $userid The user ID.
     * @return object|null The mapping.
     */
    public function get_player_mapping($userid) {
        global $DB;
        $record =  $DB->get_record('block_motrain_playermap', ['accountid' => $this->accountid, 'userid' => $userid]);
        return $record ?: null;
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
     * Get the user's metadata.
     *
     * @param object $user The user.
     * @return array First is the metadata, the other is the hash.
     */
    protected function get_user_metadata($user) {
        $data = (object) [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
        ];

        $hooks = get_plugin_list_with_function('local', 'motrain_extend_user_metadata');
        foreach ($hooks as $plugin => $fullfunctionname) {
            if (strpos($plugin, 'local_motrainaddon') !== 0) {
                continue;
            }
            try {
                component_callback($plugin, 'motrain_extend_user_metadata', [$user, $data]);
            } catch (\Exception $e) {
                debugging("Error while calling $plugin's motrain_extend_user_metadata callback: " . $e->getMessage(),
                    DEBUG_DEVELOPER);
            }
        }

        $hash = sha1(json_encode($data));
        return [$data, $hash];
    }

    /**
     * Mark all metadata as stale.
     *
     * Use this sparingly as it will cause all player metadata to be pushed to the dashboard.
     */
    public function mark_all_metadata_as_stale() {
        global $DB;
        $sql = 'UPDATE {block_motrain_playermap} SET metadatastale = ?, metadatahash = ? WHERE accountid = ?';
        $params = [1, null, $this->accountid];
        $DB->execute($sql, $params);
    }

    /**
     * Remove the mappings for a team.
     *
     * @param int $teamid The team ID.
     */
    public function remove_team($teamid) {
        global $DB;
        $DB->delete_records('block_motrain_playermap', ['accountid' => $this->accountid, 'teamid' => $teamid]);
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
     * Whether we accept foreign teams.
     *
     * @param bool $acceptforeignteams Accept foreign teams.
     */
    public function set_accept_foreign_teams($acceptforeignteams) {
        $this->acceptforeignteams = (bool) $acceptforeignteams;
    }

    /**
     * Whether we allow team changes.
     *
     * @param bool $allowteamchange Accept team change.
     */
    public function set_allow_team_change($allowteamchange) {
        $this->allowteamchange = (bool) $allowteamchange;
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

        [$playermetadata, $metadatahash] = $this->get_user_metadata($user);
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

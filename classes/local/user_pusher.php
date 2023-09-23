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
 * User pusher.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;
defined('MOODLE_INTERNAL') || die();

use block_motrain\client;
use block_motrain\local\player_mapper;
use block_motrain\local\team_resolver;

/**
 * User pusher class.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_pusher {

    /** @var string The account ID. */
    protected $accountid;
    /** @var string Whether admins can earn coins. */
    protected $adminscanearn;
    /** @var client The client. */
    protected $client;
    /** @var int The chunk size. */
    protected $chunksize = 100;
    /** @var player_mapper The player mapper. */
    protected $playermapper;
    /** @var team_resolver The team resolver. */
    protected $teamresolver;

    /**
     * Constructor.
     *
     * @param client $client The client.
     * @param string $accountid The account ID.
     * @param team_resolver $teamresolver The team resolver.
     * @param player_mapper $playermapper The player mapper.
     */
    public function __construct(client $client, $accountid, team_resolver $teamresolver, player_mapper $playermapper) {
        $this->accountid = $accountid;
        $this->client = $client;
        $this->teamresolver = $teamresolver;
        $this->playermapper = $playermapper;
    }

    /**
     * Whether we've got users to push.
     *
     * @return bool
     */
    public function count_queue() {
        global $DB;
        return $DB->count_records_select('block_motrain_userspush', '', null, 'COUNT(DISTINCT userid)');
    }

    /**
     * The chunk size.
     *
     * @return int
     */
    public function get_chunk_size() {
        return $this->chunksize;
    }

    /**
     * Whether we've got users to push.
     *
     * @return bool
     */
    public function has_queue() {
        global $DB;
        return $DB->record_exists('block_motrain_userspush', []);
    }

    /**
     * Push a chunk of users.
     *
     * @return void
     */
    public function push_chunk() {
        // We push the next one in a loop. This is inefficient for the purpose
        // of handling concurrency in the script, and as executed as background.
        for ($i = 0; $i < $this->chunksize; $i++) {
            $couldhavemore = $this->push_next();
            if (!$couldhavemore) {
                break;
            }
            // Wait for 250ms between each loop as to throttle the maximum number of calls per second.
            usleep(250 * 1000);
        }
    }

    /**
     * Push the next in the queue.
     *
     * @return bool False when the queue is empty.
     */
    protected function push_next() {
        global $DB;

        $sql = "SELECT DISTINCT u.id, u.username, u.firstname, u.lastname, u.email,
                                u.deleted, u.suspended, u.confirmed, pm.playerid
                  FROM {block_motrain_userspush} up
             LEFT JOIN {block_motrain_playermap} pm
                    ON pm.userid = up.userid
                   AND pm.accountid = :accountid
                  JOIN {user} u
                    ON u.id = up.userid
              ORDER BY u.id";
        $params = ['accountid' => $this->accountid];
        $records = $DB->get_records_sql($sql, $params, 0, 1);
        $user = reset($records);

        if (!$user) {
            return false;
        }

        $this->push_user($user);
        $DB->delete_records('block_motrain_userspush', ['userid' => $user->id]);
        return true;
    }

    /**
     * Push a user.
     *
     * @param object $user A partial user object.
     * @return void
     */
    protected function push_user($user) {
        if ($user->deleted || $user->suspended || !$user->confirmed) {
            return false;
        }

        // This is potentially inefficient but this has the benefit of being accurate.
        $teamid = $this->teamresolver->get_team_id_for_user($user->id);
        if (!$teamid) {
            return false;
        }

        try {
            $this->playermapper->get_player_id($user, $teamid);
        } catch (\moodle_exception $e) {
            debugging('An error occurred while mapping user ' . $user->id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

        return true;
    }

    /**
     * Queue a user needing a push.
     *
     * @param int $userid The user ID.
     * @return void
     */
    public function queue($userid) {
        global $DB;
        $DB->insert_record('block_motrain_userspush', (object) ['userid' => $userid]);
    }

    /**
     * Queue a whole cohort.
     *
     * @param int $cohortid The whole cohort ID.
     * @return void
     */
    public function queue_cohort($cohortid) {
        global $DB;
        $sql = "INSERT INTO {block_motrain_userspush} (userid)
                SELECT cm.userid
                  FROM {cohort_members} cm
                 WHERE cm.cohortid = :cohortid";
        $DB->execute($sql, ['cohortid' => $cohortid]);
    }

    /**
     * Queue everyone.
     *
     * @return void
     */
    public function queue_everyone() {
        global $DB;
        $sql = "INSERT INTO {block_motrain_userspush} (userid)
                SELECT u.id
                  FROM {user} u
                 WHERE u.deleted = 0
                   AND u.confirmed = 1
                   AND u.suspended = 0";
        $DB->execute($sql, []);
    }

}

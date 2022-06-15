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
 * Data provider.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\privacy;
defined('MOODLE_INTERNAL') || die();

use context;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\contextlist_base;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Data provider class.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function _get_metadata(collection $collection) {
        $collection->add_database_table('block_motrain_playermap', [
            'userid' => 'privacy:metadata:playermap:userid',
            'accountid' => 'privacy:metadata:playermap:accountid',
            'playerid' => 'privacy:metadata:playermap:playerid',
            'blocked' => 'privacy:metadata:playermap:blocked',
            'blockedreason' => 'privacy:metadata:playermap:blockedreason',
        ], 'privacy:metadata:playermap');

        $collection->add_database_table('block_motrain_log', [
            'userid' => 'privacy:metadata:log:userid',
            'contextid' => 'privacy:metadata:log:contextid',
            'coins' => 'privacy:metadata:log:coins',
            'actionname' => 'privacy:metadata:log:actionname',
            'actionhash' => 'privacy:metadata:log:actionhash',
            'timecreated' => 'privacy:metadata:log:timecreated',
            'timebroadcasted' => 'privacy:metadata:log:timebroadcasted',
            'broadcasterror' => 'privacy:metadata:log:broadcasterror'
        ], 'privacy:metadata:log');

        // No need to export data for this, it is just a queue and contains nothing.
        $collection->add_database_table('block_motrain_userspush', [
            'userid' => 'privacy:metadata:userspush:userid',
        ], 'privacy:metadata:userspush');

        // When submitting coins.
        $collection->add_external_location_link('coinsgained', [
            'coins' => 'privacy:metadata:coinsgained:coins',
            'reason' => 'privacy:metadata:coinsgained:reason',
        ], 'privacy:metadata:coinsgained');

        // When creating/searching user.
        $collection->add_external_location_link('remoteplayer', [
            'firstname' => 'privacy:metadata:remoteplayer:firstname',
            'lastname' => 'privacy:metadata:remoteplayer:lastname',
            'email' => 'privacy:metadata:remoteplayer:email',
        ], 'privacy:metadata:remoteplayer');

        return $collection;
    }
    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid($userid) {
        global $DB;
        $contextlist = new \core_privacy\local\request\contextlist();

        if ($DB->record_exists('block_motrain_playermap', ['userid' => $userid])) {
            $contextlist->add_system_context();
        }

        $sql = 'SELECT l.contextid FROM {block_motrain_log} l WHERE l.userid = ?';
        $params = [$userid];
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        $userlist->add_from_sql('userid', 'SELECT userid FROM {block_motrain_log} WHERE contextid = ?', [$context->id]);
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $userlist->add_from_sql('userid', 'SELECT userid FROM {block_motrain_playermap}', []);
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (!$contextlist->count()) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        // Export logs.
        $path = [get_string('pluginname', 'block_motrain'), get_string('privacy:path:logs', 'block_motrain')];
        $flushlogs = function($contextid, $logs) use ($path) {
            $context = context::instance_by_id($contextid);
            writer::with_context($context)->export_data($path, (object) ['data' => $logs]);
        };

        list($insql, $inparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "contextid $insql AND userid = :userid";
        $params = ['userid' => $userid] + $inparams;
        $recordset = $DB->get_recordset_select('block_motrain_log', $sql, $params, 'contextid ASC, id ASC');
        $lastcontextid = null;
        $logs = [];
        foreach ($recordset as $record) {
            if ($lastcontextid && $lastcontextid != $record->contextid) {
                $flushlogs($lastcontextid, $logs);
                $logs = [];
            }

            $logs[] = (object) [
                'userid' => transform::user($record->userid),
                'coins' => $record->coins,
                'action_name' => $record->actionname,
                'action_hash' => $record->actionhash,
                'timecreated' => $record->timecreated ? transform::date($record->timecreated) : '-',
                'timebroadcasted' => $record->timebroadcasted ? transform::date($record->timebroadcasted) : '-'
            ];
            $lastcontextid = $record->contextid;
        }

        // Flush the last iteration.
        if ($lastcontextid) {
            $flushlogs($lastcontextid, $logs);
        }
        $recordset->close();

        // Export mapping.
        $data = [];
        if (static::contains_system($contextlist)) {
            $recordset = $DB->get_recordset('block_motrain_playermap', ['userid' => $userid], 'id ASC');
            $data = [];
            foreach ($recordset as $record) {
                $data[] = (object) [
                    'userid' => transform::user($record->userid),
                    'accountid' => $record->accountid,
                    'playerid' => $record->playerid,
                    'blocked' => $record->blocked,
                    'blockedreason' => $record->blockedreason,
                ];
            }
            $recordset->close();
        }
        if (!empty($data)) {
            writer::with_context(context_system::instance())->export_data([
                get_string('pluginname', 'block_motrain'),
                get_string('privacy:path:mappings', 'block_motrain')
            ], (object) ['data' => $data]);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function _delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('block_motrain_playermap', []);
        }

        $DB->delete_records('block_motrain_log', ['contextid' => $context->id]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();
        $userid = $user->id;

        foreach ($contextlist as $context) {
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                $DB->delete_records('block_motrain_playermap', ['userid' => $userid]);
            }
            $DB->delete_records('block_motrain_log', ['contextid' => $context->id, 'userid' => $userid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        $context = $userlist->get_context();
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records_list('block_motrain_playermap', 'userid', $userids);
        }

        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = ['contextid' => $context->id] + $inparams;
        $DB->delete_records_select('block_motrain_log', "contextid = :contextid AND userid $insql", $params);
    }

    /**
     * Whether a context list contains system.
     *
     * @param contextlist_base $contextlist The list.
     */
    protected static function contains_system(contextlist_base $contextlist) {
        foreach ($contextlist as $context) {
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                return true;
            }
        }
        return false;
    }

}

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
 * Observer.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain;
defined('MOODLE_INTERNAL') || die();

use block_motrain\local\collection_strategy;
use context_system;
use moodle_exception;

require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Observer class.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Catch all.
     *
     * @param \core\event\base $event The event.
     */
    public static function catch_all(\core\event\base $event) {
        $strategy = new collection_strategy();
        $strategy->collect_event($event);
    }

    /**
     * Observes when a cohort is deleted.
     *
     * @param \core\event\cohort_deleted $event The event.
     * @return void
     */
    public static function cohort_deleted(\core\event\cohort_deleted $event) {
        // global $DB;
        // $sql = 'UPDATE {local_mootivated_school} SET cohortid = 0 WHERE cohortid = :id';
        // $DB->execute($sql, ['id' => $event->objectid]);
    }

    /**
     * Observes when a member is added to a cohort.
     *
     * @param \core\event\cohort_member_added $event The event.
     * @return void
     */
    public static function cohort_member_added(\core\event\cohort_member_added $event) {
        // global $DB;
        // if (!helper::uses_sections() || !helper::allow_automatic_role_assignment()) {
        //     return;
        // }

        // // Check if cohort is used in a school.
        // if (!$DB->record_exists('local_mootivated_school', ['cohortid' => $event->objectid])) {
        //     return;
        // }

        // // Assign the role, and catch exceptions in case the role doesn't exist or something.
        // try {
        //     $role = helper::get_mootivated_role();
        //     role_assign($role->id, $event->relateduserid, context_system::instance()->id);
        // } catch (moodle_exception $e) {
        //     debugging('Unexpected exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
        // }

        // // Queue a user to the push.
        // try {
        //     $userpusher = helper::get_user_pusher();
        //     $userpusher->queue($event->relateduserid);
        // } catch (moodle_exception $e) {
        //     debugging('Unexpected exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
        // }
    }

    /**
     * Observes when a member is removed from a cohort.
     *
     * @param \core\event\cohort_member_removed $event The event.
     * @return void
     */
    public static function cohort_member_removed(\core\event\cohort_member_removed $event) {
        // global $DB;
        // if (!helper::uses_sections() || !helper::allow_automatic_role_assignment()) {
        //     return;
        // }

        // // Check if cohort is used in a school.
        // if (!$DB->record_exists('local_mootivated_school', ['cohortid' => $event->objectid])) {
        //     return;
        // }

        // // Check if user part of any other school, if yes bail.
        // $resolver = helper::get_school_resolver();
        // if ($resolver->get_by_member($event->relateduserid) !== null) {
        //     return;
        // }

        // // Assign the role, and catch exceptions in case the role doesn't exist or something.
        // try {
        //     $role = helper::get_mootivated_role();
        //     role_unassign($role->id, $event->relateduserid, context_system::instance()->id);
        // } catch (moodle_exception $e) {
        //     debugging('Unexpected exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
        // }
    }

    /**
     * Observe when a role is assigned.
     *
     * @param \core\event\role_assigned $event The role assigned event.
     * @return void
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        // if (!helper::uses_sections()) {
        //     return;
        // }

        // // Get the role, and catch exceptions in case the role doesn't exist or something.
        // try {
        //     $role = helper::get_mootivated_role();
        // } catch (moodle_exception $e) {
        //     debugging('Unexpected exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
        //     return;
        // }

        // // If the mootivated user role was assigned, we queue the user.
        // if ($event->objectid == $role->id) {
        //     $userpusher = helper::get_user_pusher();
        //     $userpusher->queue($event->relateduserid);
        // }
    }

    /**
     * When the members of a dynamic cohort are updated.
     *
     * @param \totara_cohort\event\members_updated $event The event.
     */
    public static function totara_cohort_members_updated($event) {
        // global $DB;

        // $cohortid = $event->objectid;
        // if (!helper::uses_sections()) {
        //     return;
        // }

        // // Check if cohort is used in a school.
        // if (!$DB->record_exists('local_mootivated_school', ['cohortid' => $cohortid])) {
        //     return;
        // }

        // // Schedule the synchronisation of the school.
        // helper::schedule_school_sync($cohortid, false);
    }

    /**
     * Observe when a user is created.
     *
     * @param \core\event\user_created $event The event.
     * @return void
     */
    public static function user_created(\core\event\user_created $event) {
        // if (helper::uses_sections() || !helper::allow_automatic_role_assignment()) {
        //     return;
        // }

        // // Assign the role, and catch exceptions in case the role doesn't exist or something.
        // try {
        //     $role = helper::get_mootivated_role();
        //     role_assign($role->id, $event->relateduserid, context_system::instance()->id);
        // } catch (moodle_exception $e) {
        //     debugging('Unexpected exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
        // }
    }

}

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
        $manager = manager::instance();
        if (!$manager->is_enabled()) {
            return;
        }

        $strategy = $manager->get_collection_strategy();
        $strategy->collect_event($event);
    }

    /**
     * Observes when a cohort is deleted.
     *
     * @param \core\event\cohort_deleted $event The event.
     * @return void
     */
    public static function cohort_deleted(\core\event\cohort_deleted $event) {
        global $DB;
        $DB->delete_records('block_motrain_teammap', ['cohortid' => $event->objectid]);
    }

    /**
     * Observes when a member is added to a cohort.
     *
     * @param \core\event\cohort_member_added $event The event.
     * @return void
     */
    public static function cohort_member_added(\core\event\cohort_member_added $event) {
        global $DB;

        $manager = manager::instance();
        if (!$manager->is_enabled()) {
            return;
        } else if (!$manager->is_using_cohorts()) {
            return;
        }

        // Check whether we should even bother.
        if (!$manager->is_automatic_push_enabled()) {
            return;
        }

        // Check if cohort is used.
        if (!$DB->record_exists('block_motrain_teammap', ['cohortid' => $event->objectid])) {
            return;
        }

        // Queue a user to the push.
        try {
            $userpusher = $manager->get_user_pusher();
            $userpusher->queue($event->relateduserid);
        } catch (moodle_exception $e) {
            debugging('Unexpected exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * When the members of a dynamic cohort are updated.
     *
     * @param \totara_cohort\event\members_updated $event The event.
     */
    public static function totara_cohort_members_updated($event) {
        global $DB;

        $manager = manager::instance();
        if (!$manager->is_enabled()) {
            return;
        } else if (!$manager->is_using_cohorts()) {
            return;
        }

        // Check whether we should even bother.
        if (!$manager->is_automatic_push_enabled()) {
            return;
        }

        // Check if cohort is used.
        if (!$DB->record_exists('block_motrain_teammap', ['cohortid' => $event->objectid])) {
            return;
        }

        // Schedule the synchronisation of the cohort.
        $manager->schedule_cohort_sync($event->objectid, false);
    }

}

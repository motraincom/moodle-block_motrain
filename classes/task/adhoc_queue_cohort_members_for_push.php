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
 * Task.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\task;
defined('MOODLE_INTERNAL') || die();

use block_motrain\manager;
use core_component;

/**
 * Task.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_queue_cohort_members_for_push extends \core\task\adhoc_task {

    /**
     * Execute.
     *
     * @return void
     */
    public function execute() {
        global $CFG, $DB;

        $manager = manager::instance();
        if (!$manager->is_enabled()) {
            mtrace('Motrain is not enabled.');
            return;
        } else if (!$manager->is_using_cohorts()) {
            mtrace('System does not use cohorts.');
            return;
        }

        $data = $this->get_custom_data();
        if (empty($data) || empty($data->cohortid)) {
            mtrace('Missing cohort ID in custom data.');
            return;
        }
        $cohortid = $data->cohortid;
        $totarasync = !empty($data->totarasync);

        // When the cohort is a dynamic cohort from Totara, we should update its members first.
        // This is conditional to $totarasync because the Totara code will trigger an event,
        // which we observe to register this task, and so we might end-up in an infinite loop
        // if we do not ignore the Totara sync from the observer.
        if (core_component::is_core_subsystem('totara') && $totarasync) {

            // Fetch the cohort to check its type.
            $cohort = $DB->get_record('cohort', ['id' => $cohortid]);
            if (empty($cohort)) {
                mtrace('Cohort not found.');
                return;
            }

            // When dynamic cohort, synchronise its members.
            require_once($CFG->dirroot . '/totara/cohort/lib.php');
            if ($cohort->cohorttype == \cohort::TYPE_DYNAMIC) {
                mtrace(sprintf('Syncing dynamic users of cohort %d.', $cohortid));
                $trace = new \text_progress_trace();
                totara_cohort_check_and_update_dynamic_cohort_members(0, $trace, $cohortid);
            }
        }

        // Flag all members to be pushed.
        mtrace(sprintf('Queuing users from cohort %d.', $cohortid));
        $pusher = $manager->get_user_pusher();
        $pusher->queue_cohort($cohortid);
    }

}

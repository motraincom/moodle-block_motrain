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
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\task;
defined('MOODLE_INTERNAL') || die();

use block_motrain\manager;
use context_course;

/**
 * Task.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_process_bulk_completion_import extends \core\task\adhoc_task {

    /**
     * Execute.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $manager = manager::instance();
        if (!$manager->is_enabled()) {
            mtrace('Incentli is not enabled.');
            return;
        } else if ($manager->is_paused()) {
            mtrace('Incentli is paused.');
            return;
        }

        // This is expected to match ['userid' => int, 'courseid' => int][] as referenced in
        // the function trigger_course_completions_imported_event from totara/completionimport/lib.php.
        $data = $this->get_custom_data();
        if (empty($data) || !is_array($data)) {
            mtrace('Nothing to process in custom data.');
            return;
        }

        // Walking through each of the entries.
        $collectionstrategy = $manager->get_collection_strategy();
        foreach ($data as $entry) {
            $userid = $entry->userid;
            $courseid = $entry->courseid;

            // Check that the user can earn in course.
            $context = context_course::instance($courseid, IGNORE_MISSING);
            if (!$context || !$manager->can_earn_in_context($userid, $context)) {
                continue;
            }

            // Validate that the user has completed the course.
            $status = $DB->get_field('course_completions', 'status', ['userid' => $userid, 'course' => $courseid]);
            $iscomplete = in_array((int) $status, [COMPLETION_STATUS_COMPLETE, COMPLETION_STATUS_COMPLETEVIARPL]);
            if (!$iscomplete) {
                continue;
            }

            // Pass on the processing to the collection strategy.
            $collectionstrategy->handle_award_for_course_completion($userid, $courseid);

            // Prevent hammering the server by throttling to 10 per seconds (1 every 100ms).
            usleep(100 * 1000);
        }
    }

}

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
 * External.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain;

use completion_info;
use context_course;
use context_system;
use course_modinfo;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {


    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_activities_with_completion_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course ID')
        ]);
    }

    /**
     * Get all activities.
     *
     * @return array
     */
    public static function get_activities_with_completion($courseid) {
        global $CFG;
        require_once($CFG->dirroot . '/course/externallib.php');
        require_once($CFG->libdir . '/completionlib.php');

        $params = self::validate_parameters(self::get_activities_with_completion_parameters(), ['courseid' => $courseid]);
        $courseid = $params['courseid'];

        $context = context_course::instance($courseid);
        self::validate_context($context);
        // This is only meant to be used by admins in the admin UI.
        require_capability('moodle/site:config', context_system::instance());

        $course = get_course($courseid);
        $modinfo = course_modinfo::instance($courseid);
        $completion = new completion_info($course);

        return array_reduce(
            array_filter($modinfo->get_cms(), function($cminfo) use ($completion) {
                return $completion->is_enabled($cminfo);
            }),
            function($carry, $cminfo) {
                $contextid = $cminfo->context->id;
                $carry[] = [
                    'cmid' => (int) $cminfo->id,
                    'contextid' => (int) $contextid,
                    'name' => external_format_string($cminfo->name, $contextid),
                    'module' => (string) $cminfo->modname,
                ];
                return $carry;
            },
        []);
    }

    /**
     * External function return definition.
     *
     * @return external_single_structure
     */
    public static function get_activities_with_completion_returns() {
        return new external_multiple_structure(new external_single_structure([
            'cmid' => new external_value(PARAM_INT, 'The course module ID'),
            'contextid' => new external_value(PARAM_INT, 'The module context ID'),
            'name' => new external_value(PARAM_TEXT, 'The name'),
            'module' => new external_value(PARAM_TEXT, 'The module type (assign, forum, ...)')
        ]));
    }

}
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

use block_motrain\local\award\award;
use block_motrain\local\completion_coins_calculator;
use block_motrain\local\helper;
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
    public static function award_coins_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'The user ID'),
            'coins' => new external_value(PARAM_INT, 'The number of coins'),
        ]);
    }

    /**
     * External function.
     *
     * @return array
     */
    public static function award_coins($userid, $coins) {
        $params = self::validate_parameters(self::award_coins_parameters(), ['userid' => $userid, 'coins' => $coins]);
        $userid = $params['userid'];
        $coins = $params['coins'];

        $context = context_system::instance();
        self::validate_context($context);

        $manager = manager::instance();
        $manager->require_enabled();
        $manager->require_not_paused();
        $manager->require_award_coins();
        $manager->require_player($userid);

        $award = new award($userid, SYSCONTEXTID, 'ws');
        $award->set_strict(true);
        $success = $award->give($coins);

        return [
            'success' => $success,
        ];
    }

    /**
     * External function return definition.
     *
     * @return external_single_structure
     */
    public static function award_coins_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL),
        ]);
    }

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
                    'name' => helper::external_format_string_unescaped($cminfo->name, $contextid),
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

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function save_completion_rules_parameters() {
        return new external_function_parameters([
            'global' => new external_single_structure([
                'course' => new external_value(PARAM_INT, 'The number of coins', VALUE_DEFAULT, 0),
                'modules' => new external_multiple_structure(new external_single_structure([
                    'module' => new external_value(PARAM_ALPHANUMEXT, 'The module type (assign, forum, ...)'),
                    'coins' => new external_value(PARAM_INT, 'The number of coins'),
                ]), '', VALUE_DEFAULT, []),
                'userecommended' => new external_value(PARAM_BOOL, 'Whether to use the recommended values', VALUE_DEFAULT, false)
            ]),
            'rules' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The course ID'),
                'coins' => new external_value(PARAM_INT, 'The number of coins, or null.'),
                'cms' => new external_multiple_structure(new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'The course module ID'),
                    'coins' => new external_value(PARAM_INT, 'The number of coins, or null.'),
                ]), '', VALUE_DEFAULT, [])
            ])),
        ]);
    }

    /**
     * Get all activities.
     *
     * @return array
     */
    public static function save_completion_rules($global, $rules) {
        global $DB;

        $params = self::validate_parameters(self::save_completion_rules_parameters(), ['global' => $global, 'rules' => $rules]);
        $rules = $params['rules'];
        $global = $params['global'];

        // This is only meant to be used by admins in the admin UI.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        // Save the global rules.
        if (empty($global) || !empty($global['userecommended'])) {
            // We use the recommended values, delete all.
            $DB->delete_records('block_motrain_comprules', ['courseid' => 0]);
        } else {

            $globalrecords = $DB->get_records('block_motrain_comprules', ['courseid' => 0]);
            $organised = [];
            foreach ($globalrecords as $record) {
                if (empty($record->modname)) {
                    $organised['_course'] = $record;
                } else {
                    $organised[$record->modname] = $record;
                }
            }

            // Course coins.
            if (!empty($organised['_course'])) {
                $record = $organised['_course'];
            } else {
                $record = (object) ['courseid' => 0, 'modname' => null];
            }
            $record->coins = max(0, (int) $global['course']);
            if (!empty($record->id)) {
                $DB->update_record('block_motrain_comprules', $record);
            } else {
                $DB->insert_record('block_motrain_comprules', $record);
            }

            // Module coins.
            $modulesseen = [];
            foreach ($global['modules'] as $module) {
                $modulesseen[] = $module['module'];
                if (!empty($organised[$module['module']])) {
                    $record = $organised[$module['module']];
                } else {
                    $record = (object) ['courseid' => 0, 'modname' => $module['module']];
                }
                $record->coins = max(0, (int) $module['coins']);
                if (!empty($record->id)) {
                    $DB->update_record('block_motrain_comprules', $record);
                } else {
                    $DB->insert_record('block_motrain_comprules', $record);
                }
            }
        }

        if (empty($rules)) {
            // No rules, delete all of them.
            $DB->delete_records_select('block_motrain_comprules', 'courseid != ?', [0]);

        } else {
            $todeleteids = [];
            $rulecourseids = [];

            $courserecords = $DB->get_recordset_select('block_motrain_comprules', 'courseid != ?', [0]);
            $organised = [];
            foreach ($courserecords as $record) {
                if (!isset($organised[$record->courseid])) {
                    $organised[$record->courseid] = (object) [
                        'course' => null,
                        'cms' => []
                    ];
                }
                if (empty($record->cmid)) {
                    $organised[$record->courseid]->course = $record;
                } else {
                    $organised[$record->courseid]->cms[$record->cmid] = $record;
                }
            }
            $courserecords->close();

            // For each of the course rules.
            foreach ($rules as $rule) {
                $courseid = $rule['id'];
                $rulecourseids[] = $courseid;
                $coursedata = !empty($organised[$courseid]) ? $organised[$courseid] : null;

                // Update the course completion value.
                $courserecord = !empty($coursedata) ? $coursedata->course : null;
                if ($rule['coins'] === null && $courserecord) {
                    $todeleteids[] = $courserecord->id;
                } else if ($rule['coins'] !== null) {
                    if (!$courserecord) {
                        $courserecord = (object) ['courseid' => $courseid, 'cmid' => 0, 'coins' => 0];
                    }
                    $courserecord->coins = $rule['coins'];
                    if (!empty($courserecord->id)) {
                        $DB->update_record('block_motrain_comprules', $courserecord);
                    } else {
                        $DB->insert_record('block_motrain_comprules', $courserecord);
                    }
                }

                // Update each course module.
                $cmseens = [];
                $cmsdata = !empty($coursedata) ? $coursedata->cms : [];
                foreach ($rule['cms'] as $cmrule) {
                    $cmid = $cmrule['id'];
                    $cmseens[] = $cmid;
                    $cmrecord = !empty($cmsdata[$cmid]) ? $cmsdata[$cmid] : null;
                    if ($cmrule['coins'] === null && $cmrecord) {
                        $todeleteids[] = $cmrecord->id;
                    } else if ($cmrule['coins'] !== null) {
                        if (!$cmrecord) {
                            $cmrecord = (object) ['courseid' => $courseid, 'cmid' => $cmid, 'coins' => 0];
                        }
                        $cmrecord->coins = $cmrule['coins'];
                        if (!empty($cmrecord->id)) {
                            $DB->update_record('block_motrain_comprules', $cmrecord);
                        } else {
                            $DB->insert_record('block_motrain_comprules', $cmrecord);
                        }
                    }
                }

                // Delete the cmids we have not seen in the rules.
                $cmstodelete = array_diff_key($cmsdata, array_flip($cmseens));
                if (!empty($cmstodelete)) {
                    $todeleteids = array_merge($todeleteids, array_values(array_map(function($record) {
                        return $record->id;
                    }, $cmstodelete)));
                }
            }

            // Flag the courses that have been removed to be deleted.
            $extraneousrecords = array_diff_key($organised, array_flip($rulecourseids));
            foreach ($extraneousrecords as $extraneousrecord) {
                if (!empty($extraneousrecord->course)) {
                    $todeleteids[] = $extraneousrecord->course->id;
                }
                if (!empty($extraneousrecord->cms)) {
                    $todeleteids = array_merge($todeleteids, array_values(array_map(function($record) {
                        return $record->id;
                    }, $extraneousrecord->cms)));
                }
            }

            // Remove obsolete rules.
            if (!empty($todeleteids)) {
                $DB->delete_records_list('block_motrain_comprules', 'id', $todeleteids);
            }
        }

        // Purge the cache.
        $completioncoinscalculator = new completion_coins_calculator();
        $completioncoinscalculator->purge_cache();

        return ['success' => true];
    }

    /**
     * External function return definition.
     *
     * @return external_single_structure
     */
    public static function save_completion_rules_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL),
        ]);
    }
}

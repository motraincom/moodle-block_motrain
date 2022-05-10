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
 * Completion coins calculator.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use block_motrain\manager;
use cache;
use cache_store;
use context;
use core\event\course_completed;
use core\event\course_module_completion_updated;
use core_user;
use course_modinfo;
use local_mootivated\local\lang_reason;

defined('MOODLE_INTERNAL') || die();


/**
 * Completion coins calculator.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_coins_calculator {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->cache = cache::make('block_motrain', 'comprules');
    }

    /**
     * Get the coins for a course.
     *
     * @param int $courseid The course ID.
     * @return int
     */
    public function get_course_coins($courseid) {
        $rule = $this->get_course_rule($courseid);
        if (isset($rule->coins)) {
            return $rule->coins;
        }
        return $this->get_course_default();
    }

    /**
     * Get the coins for a module.
     *
     * @param int $courseid The course ID.
     * @param int $cmid The corresponding CM ID.
     * @return int
     */
    public function get_module_coins($courseid, $cmid) {
        $rule = $this->get_course_rule($courseid);
        if (isset($rule->cms[$cmid])) {
            return $rule->cms[$cmid];
        }

        $modinfo = get_fast_modinfo($courseid);
        $cminfo = $modinfo->get_cm($cmid);
        $modname = $cminfo->modname;

        return $this->get_module_default($modname);
    }

    /**
     * Load the course default.
     *
     * @return int
     */
    protected function get_course_default() {
        $globalrule = $this->get_global_rule();
        return !empty($globalrule->course) ? $globalrule->course : 0;
    }

    /**
     * Get the course rule.
     *
     * @return object
     */
    protected function get_course_rule($courseid) {
        $courseid = max(1, (int) $courseid); // Prevent 0, as reserved for global.
        if ($rule = $this->cache->get($courseid) === false) {
            $rule = $this->load_course_rule($courseid);
            $this->cache->set($courseid, $rule);
        }
        return $rule;
    }

    /**
     * Get the global rule.
     *
     * @return object
     */
    protected function get_global_rule() {
        $courseid = 0;
        if ($rule = $this->cache->get($courseid) === false) {
            $rule = $this->load_global_rule();
            $this->cache->set($courseid, $rule);
        }
        return $rule;
    }

    /**
     * Load the module default.
     *
     * @return int
     */
    protected function get_module_default($modname) {
        $globalrule = $this->get_global_rule();
        if (!empty($globalrule->modules[$modname])) {
            return $globalrule->modules[$modname];
        }
        return 0;
    }

    /**
     * Load the course rule.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    protected function load_course_rule($courseid) {
        global $DB;

        $rule = (object) ['coins' => null, 'cms' => []];
        $records = $DB->get_records('block_motrain_comprules', ['courseid' => $courseid]);
        foreach ($records as $record) {
            if (empty($record->cmid)) {
                $rule->coins = (int) $record->coins;
                continue;
            }
            $rule->cms[$record->cmid] = (int) $record->coins;
        }
        return $rule;
    }

    /**
     * Load the global rule.
     *
     * @return object
     */
    protected function load_global_rule() {
        global $DB;

        $userecommended = true;
        $rule = (object) ['course' => null, 'modules' => []];
        $records = $DB->get_records('block_motrain_comprules', ['courseid' => 0]);
        foreach ($records as $record) {
            $userecommended = false;
            if (empty($record->modname)) {
                $rule->course = (int) $record->coins;
                continue;
            }
            $rule->modules[$record->modname] = (int) $record->coins;
        }

        if ($userecommended) {
            $rule = static::get_recommended();
        }

        return $rule;
    }

    /**
     * Get the recommended global values.
     *
     * @return object
     */
    public static function get_recommended() {
        // This has the same format as the global rule.
        return (object) [
            'course' => 30,
            'modules' => [
                'quiz' => 15,
                'lesson' => 15,
                'scorm' => 15,
                'assign' => 15,
                'forum' => 15,
                'feedback' => 10,
                'questionnaire' => 10,
                'workshop' => 10,
                'glossary' => 10,
                'database' => 10,
                'journal' => 10,
                'hotpot' => 10,
                'book' => 2,
                'resource' => 2,
                'folder' => 2,
                'imscp' => 2,
                'label' => 2,
                'page' => 2,
                'url' => 2
            ]
        ];
    }

    /**
     * Read all the rules.
     *
     * @return object
     */
    public static function get_all_rules() {
        global $DB;

        $globalrules = (object) ['course' => null, 'modules' => []];
        $rules = [];

        $recordset = $DB->get_recordset('block_motrain_comprules', []);
        foreach ($recordset as $record) {
            if (empty($record->courseid)) {
                if (empty($record->modname)) {
                    $globalrules->course = (int) $record->coins;
                } else {
                    $globalrules->modules[] = [
                        'module' => $record->modname,
                        'coins' => (int) $record->coins,
                    ];
                }
                continue;
            }

            if (empty($rules[$record->courseid])) {
                $rules[$record->courseid] = (object) ['id' => (int) $record->courseid, 'coins' => null, 'cms' => []];
            }
            if (empty($record->cmid)) {
                $rules[$record->courseid]->coins = (int) $record->coins;
            } else {
                $rules[$record->courseid]->cms[] = [
                    'id' => (int) $record->cmid,
                    'coins' => (int) $record->coins
                ];
            }
        }
        $recordset->close();

        return (object) [
            'global' => (object) [
                'course' => $globalrules->course,
                'modules' => array_values($globalrules->modules),
            ],
            'rules' => array_values($rules),
        ];
    }

}

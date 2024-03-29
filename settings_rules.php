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
 * Settings.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\local\completion_coins_calculator;
use block_motrain\local\helper;
use block_motrain\local\program_coins_calculator;
use block_motrain\manager;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

$motrainteamid = optional_param('id', null, PARAM_INT);
$deleteid = optional_param('delete', null, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

require_login();
$manager = manager::instance();
$manager->require_manage();

$PAGE->set_url('/blocks/motrain/settings_rules.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('coinrules', 'block_motrain'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('block_motrain');

// Get the list of courses (performance can be improved).
$courses = $DB->get_records('course', ['enablecompletion' => 1], 'shortname, fullname, id', 'id, shortname, fullname');
$courses = array_values(array_map(function($course) {
    $context = context_course::instance($course->id);
    return (object) [
        'id' => (int) $course->id,
        'displayname' => helper::format_string_unescaped(get_course_display_name_for_list($course), $context)
    ];
}, $courses));

// Prepare the list of modules.
$moduletypenames = [];
if (method_exists(\container_course\course_helper::class, 'get_all_modules')) {
    $moduletypenames = \container_course\course_helper::get_all_modules();
} else {
    $moduletypenames = get_module_types_names();
}
$modules = [];
foreach ($moduletypenames as $mod => $modname) {
    $modules[] = (object) [
        'module' => (string) $mod,
        'name' => (string) $modname
    ];
}

// Load the rules.
$allrules = completion_coins_calculator::get_all_rules();

// Display the page.
echo $output->header();
echo $output->heading(get_string('pluginname', 'block_motrain'));
echo $output->navigation_for_managers($manager, 'rules');

if (!$manager->is_enabled()) {
    echo $output->notification(get_string('pluginnotenabledseesettings', 'block_motrain'));
}

if ($manager->is_totara()) {
    echo $output->heading(get_string('courseandactivitycompletion', 'block_motrain'));
}

echo $output->react_module('block_motrain/ui-completion-rules-lazy', [
    'courses' => $courses,
    'modules' => $modules,
    'defaults' => completion_coins_calculator::get_recommended(),
    'globalRules' => $allrules->global,
    'rules' => $allrules->rules,
]);

if ($manager->is_totara()) {
    require_once($CFG->dirroot . '/totara/program/lib.php');

    $programs = prog_get_programs("all", 'p.fullname ASC, p.id ASC');
    $programs = array_values(array_map(function($program) {
        $context = context_program::instance($program->id);
        return (object) [
            'id' => (int) $program->id,
            'displayname' => helper::format_string_unescaped($program->fullname, $context)
        ];
    }, $programs));

    $allrules = program_coins_calculator::get_all_rules();

    echo $output->heading(get_string('programcompletion', 'block_motrain'));
    echo $output->react_module('block_motrain/ui-program-rules-lazy', [
        'programs' => $programs,
        'defaults' => program_coins_calculator::get_recommended(),
        'globalRules' => $allrules->global,
        'rules' => $allrules->rules,
    ]);
}

echo $output->footer();

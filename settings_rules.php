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

use block_motrain\form\team_form;
use block_motrain\local\completion_coins_calculator;
use block_motrain\manager;
use core\output\notification;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$motrainteamid = optional_param('id', null, PARAM_INT);
$deleteid = optional_param('delete', null, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

admin_externalpage_setup('block_motrain_rules');

$output = $PAGE->get_renderer('block_motrain');
$manager = manager::instance();

// Get the list of courses (performance can be improved).
$courses = $DB->get_records('course', ['enablecompletion' => 1], 'shortname, fullname, id', 'id, shortname, fullname');
$courses = array_values(array_map(function($course) {
    $context = context_course::instance($course->id);
    return (object) [
        'id' => (int) $course->id,
        'displayname' => format_string(get_course_display_name_for_list($course), true,
            ['context' => $context, 'escape' => false])
    ];
}, $courses));

// Prepare the list of modules.
$modules = [];
foreach (get_module_types_names() as $mod => $modname) {
    $modules[] = (object) [
        'module' => (string) $mod,
        'name' => (string) $modname
    ];
}

// Load the rules.
$allrules = completion_coins_calculator::get_all_rules();

// Display the page.
echo $output->header();
echo $output->heading(get_string('coinrules', 'block_motrain'));

if (!$manager->is_enabled()) {
    echo $output->notification(get_string('pluginnotenabledseesettings', 'block_motrain'));
}

echo $output->react_module('block_motrain/ui-completion-rules-lazy', [
    'courses' => $courses,
    'modules' => $modules,
    'defaults' => completion_coins_calculator::get_recommended(),
    'globalRules' => $allrules->global,
    'rules' => $allrules->rules,
]);

echo $output->footer();

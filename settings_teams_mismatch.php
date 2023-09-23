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
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\form\team_form;
use block_motrain\manager;
use block_motrain\output\users_multi_teams_table;
use core\output\notification;

require_once(__DIR__ . '/../../config.php');

require_login();
$manager = manager::instance();
$manager->require_manage();

$PAGE->set_url('/blocks/motrain/settings_teams_mismatch.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('teams', 'block_motrain'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('block_motrain');
$returnurl = new moodle_url('/blocks/motrain/settings_teams.php');

if (!$manager->is_enabled()) {
    echo $output->header();
    echo $output->heading(get_string('pluginname', 'block_motrain'));
    echo $output->navigation_for_managers($manager, 'teams');
    echo $output->notification(get_string('pluginnotenabledseesettings', 'block_motrain'));
    echo $output->footer();
    die();
}

// Display the page.
echo $output->header();
echo $output->heading(get_string('teams', 'block_motrain'));
echo $output->navigation_for_managers($manager, 'teams');

echo $output->render_from_template('block_motrain/heading', [
    'backurl' => $returnurl->out(false),
    'title' => get_string('multiteamsusers', 'block_motrain'),
]);

$table = new users_multi_teams_table($manager);
$table->define_baseurl($PAGE->url);
$table->out(50, true);

echo $output->footer();

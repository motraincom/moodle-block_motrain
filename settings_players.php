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

use block_motrain\manager;
use block_motrain\output\players_mapping_table;

require_once(__DIR__ . '/../../config.php');

$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$userid = optional_param('userid', null, PARAM_ALPHANUMEXT);

require_login();
$manager = manager::instance();
$manager->require_manage();

$PAGE->set_url('/blocks/motrain/settings_players.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'block_motrain'));
$PAGE->set_title(get_string('playersmapping', 'block_motrain'));

$output = $PAGE->get_renderer('block_motrain');

if (!$manager->is_enabled()) {
    echo $output->header();
    echo $output->navigation_for_managers($manager, 'players');
    echo $output->notification(get_string('pluginnotenabledseesettings', 'block_motrain'));
    echo $output->footer();
    die();
}

if ($action === 'delete' && confirm_sesskey()) {
    $manager->get_player_mapper()->remove_user($userid);
    redirect($PAGE->url);
} else if ($action === 'reset' && confirm_sesskey()) {
    $manager->get_player_mapper()->unblock_user($userid);
    redirect($PAGE->url);
}

// Display the page.
echo $output->header();
echo $output->navigation_for_managers($manager, 'players');
echo html_writer::tag('p', get_string('playermappingintro', 'block_motrain'));
$table = new players_mapping_table($manager);
$table->define_baseurl($PAGE->url);
$table->out(50, true);
echo $output->footer();

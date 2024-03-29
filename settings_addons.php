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
 * List add-ons.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\addons;
use block_motrain\manager;

require_once(__DIR__ . ' /../../config.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$manager = manager::instance();
$manager->require_manage();

$PAGE->set_url('/blocks/motrain/settings_addons.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('manageaddons', 'block_motrain'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('block_motrain');

echo $output->header();
echo $output->heading(get_string('pluginname', 'block_motrain'));
echo $output->navigation_for_managers($manager, 'addons');

$addons = addons::get_addons();
if (empty($addons)) {
    echo $output->notification(get_string('noaddoninstalled', 'block_motrain'), 'nofityinfo');
    echo $output->footer();
    die();
}

$table = new flexible_table('block_motrain_addons_administration_table');
$table->define_columns(['name', 'enabled', 'settings']);
$table->define_headers([
    get_string('addon', 'block_motrain'),
    get_string('addonstate', 'block_motrain'),
    '',
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute('id', 'localplugins');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$plugins = [];
foreach ($addons as $plugin) {
    $plugins[$plugin->displayname] = $plugin;
}
core_collator::ksort($plugins);

foreach ($plugins as $name => $plugin) {

    $settingslink = '';
    $settingsurl = $plugin->settingsurl;
    if (!empty($settingsurl)) {
        $settingslink = html_writer::link($settingsurl, get_string('setup', 'block_motrain'));
    }

    $enabledstr = $plugin->enabled ? get_string('enabled', 'block_motrain') : get_string('disabled', 'block_motrain');
    $summary = html_writer::div($name) . html_writer::div( html_writer::tag('small',
        get_string('plugindescription', $plugin->component)), 'mt-1 text-muted'
    );

    $table->add_data([$summary, $enabledstr, $settingslink]);
}

$table->finish_output();

echo $output->footer();

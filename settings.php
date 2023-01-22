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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/motrain/lib.php');

$settings = new admin_externalpage('blocksettingmotrain', get_string('settings', 'core'),
    new moodle_url('/blocks/motrain/settings_config.php'), 'block/motrain:manage');

$category = new admin_category('block_motrain_category', get_string('pluginname', 'block_motrain'));
$category->add('block_motrain_category', $settings);

// Swap the category for the main settings page.
$settingspage = $settings;
$settings = $category;

// Add page to manage the coin rules.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_rules',
    get_string('coinrules', 'block_motrain'), new moodle_url('/blocks/motrain/settings_rules.php'), 'block/motrain:manage'));

// Add page to manage the teams.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_teams',
    get_string('teamassociations', 'block_motrain'), new moodle_url('/blocks/motrain/settings_teams.php'), 'block/motrain:manage'));

// Add page to view the player mapping.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_players',
    get_string('playersmapping', 'block_motrain'), new moodle_url('/blocks/motrain/settings_players.php'), 'block/motrain:manage'));

// Add page to view the message templates.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_message_templates',
    get_string('messagetemplates', 'block_motrain'), new moodle_url('/blocks/motrain/settings_message_templates.php'),
    'block/motrain:manage'));

// Add page to manage add-ons.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_manageaddons', new lang_string('manageaddons',
    'block_motrain'), new moodle_url('/blocks/motrain/settings_addons.php'), 'block/motrain:manage'));

// Add the add-ons category settings.
$addoncategory = new admin_category('block_motrain_addons', new lang_string('motrainaddons', 'block_motrain'));
$category->add('block_motrain_category', $addoncategory);

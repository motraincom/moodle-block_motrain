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

$category = new admin_category('block_motrain_category', get_string('pluginname', 'block_motrain'));
$category->add('block_motrain_category', $settings);

// Swap the category for the main settings page.
$settingspage = $settings;
$settingspage->visiblename = get_string('settings', 'core');
$settings = $category;

// Add page to manage the teams.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_teams',
    get_string('teamassociations', 'block_motrain'), new moodle_url('/blocks/motrain/settings_teams.php')));

// Add page to manage the coin rules.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_rules',
    get_string('coinrules', 'block_motrain'), new moodle_url('/blocks/motrain/settings_rules.php')));

// Add page to view the player mapping.
$category->add('block_motrain_category', new admin_externalpage('block_motrain_players',
    get_string('playersmapping', 'block_motrain'), new moodle_url('/blocks/motrain/settings_players.php')));

// Add the admin settings.
if ($hassiteconfig) {

    $settingspage->add(new block_motrain\local\setting\is_enabled());

    $setting = new admin_setting_configtext('block_motrain/accountid', get_string('accountid', 'block_motrain'),
        get_string('accountid_desc', 'block_motrain'), '');
    $setting->set_updatedcallback('block_motrain_check_enabled_state');
    $settingspage->add($setting);

    $hosts = [
        'https://api.motrainapp.com' => 'api.motrainapp.com',
        'https://api.eu.motrainapp.com' => 'api.eu.motrainapp.com',
        'https://api.dev.motrainapp.com' => 'api.dev.motrainapp.com',
    ];
    $setting = new admin_setting_configselect('block_motrain/apihost', get_string('apihost', 'block_motrain'),
        get_string('apihost_desc', 'block_motrain'), array_keys($hosts)[0], $hosts);
    $setting->set_updatedcallback('block_motrain_check_enabled_state');
    $settingspage->add($setting);

    $setting = new admin_setting_configpasswordunmask('block_motrain/apikey', get_string('apikey', 'block_motrain'),
        get_string('apikey_desc', 'block_motrain'), '');
    $setting->set_updatedcallback('block_motrain_check_enabled_state');
    $settingspage->add($setting);

    $setting = new admin_setting_configcheckbox('block_motrain/usecohorts', get_string('usecohorts', 'block_motrain'),
        get_string('usecohorts_help', 'block_motrain'), true);
    $settingspage->add($setting);

    $settingspage->add(new admin_setting_configcheckbox('block_motrain/adminscanearn',
        get_string('adminscanearn', 'block_motrain'), get_string('adminscanearn_desc', 'block_motrain'), false));

    $setting = new admin_setting_configcheckbox('block_motrain/autopush', get_string('autopush', 'block_motrain'),
        get_string('autopush_help', 'block_motrain'), false);
    $settingspage->add($setting);

    $setting = new admin_setting_configstoredfile('block_motrain/coinsimage', get_string('coinsimage', 'block_motrain'),
        get_string('coinsimage_help', 'block_motrain'), 'coinsimage', 0, ['accepted_types' => ['image']]);
    $settingspage->add($setting);

}

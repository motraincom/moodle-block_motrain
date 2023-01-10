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

use block_motrain\local\setting\static_content;
use block_motrain\manager;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/blocks/motrain/lib.php');

require_login();
$manager = manager::instance();
$manager->require_manage();

$PAGE->set_url('/blocks/motrain/settings_config.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('settings', 'core'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('block_motrain');

// Generate the list of settings. This re-implements the adminsetting_page behaviour of the settings page
// in order to stay consistent with what we had before (when admin settings were required to edit the page).
// Unfortunately, this means taht the settings cannot be searched in the UI. This also means that they do not
// benefit from getting the default values set during the installation.
$settingspage = new admin_settingpage('block_motrain_settings', get_string('settings', 'core'), 'moodle/motrain:manage');

$settingspage->add(new block_motrain\local\setting\is_enabled());

$setting = new admin_setting_configcheckbox('block_motrain/ispaused', get_string('ispaused', 'block_motrain'),
    get_string('ispaused_help', 'block_motrain'), false);
$setting->set_updatedcallback('block_motrain_ispaused_updated_hook');
$settingspage->add($setting);

$setting = new admin_setting_configtext('block_motrain/accountid', get_string('accountid', 'block_motrain'),
    get_string('accountid_desc', 'block_motrain'), '');
$setting->set_updatedcallback('block_motrain_accountid_updated_hook');
$settingspage->add($setting);

$hosts = [
    'https://api.motrainapp.com' => 'api.motrainapp.com',
    'https://api.eu.motrainapp.com' => 'api.eu.motrainapp.com'
];
$setting = new admin_setting_configselect('block_motrain/apihost', get_string('apihost', 'block_motrain'),
    get_string('apihost_desc', 'block_motrain'), array_keys($hosts)[0], $hosts);
$setting->set_updatedcallback('block_motrain_check_enabled_state');
$settingspage->add($setting);

$setting = new admin_setting_configpasswordunmask('block_motrain/apikey', get_string('apikey', 'block_motrain'),
    get_string('apikey_desc', 'block_motrain'), '');
$setting->set_updatedcallback('block_motrain_check_enabled_state');
$settingspage->add($setting);

$setting = new static_content('block_motrain/metadatacache', get_string('metadatacache', 'block_motrain'),
    get_string('metadatacache_help', 'block_motrain'),
        html_writer::link(new moodle_url('/blocks/motrain/settings_action.php', ['action' => 'purgemetadata',
            'sesskey' => sesskey(), 'returnurl' => $PAGE->has_set_url() ? $PAGE->url->out_as_local_url() : '/']),
            get_string('purgecache', 'block_motrain'))
);
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

// Save the data.
if ($data = data_submitted() and confirm_sesskey()) {
    $data = array_filter((array) $data, function($key) {
        return strpos($key, 's_') === 0;
    }, ARRAY_FILTER_USE_KEY);

    $adminroot = admin_get_root();
    $count = 0;
    foreach ($settingspage->settings as $setting) {
        $fullname = $setting->get_full_name();
        if ($setting->nosave || !array_key_exists($fullname, $data)) {
            continue;
        }
        $original = $setting->get_setting();
        $error = $setting->write_setting($data[$fullname]);
        if ($error !== '') {
            $adminroot->errors[$fullname] = new stdClass();
            $adminroot->errors[$fullname]->data  = $data[$fullname];
            $adminroot->errors[$fullname]->id    = $setting->get_id();
            $adminroot->errors[$fullname]->error = $error;
        } else {
            $setting->write_setting_flags($data);
        }
        if ($setting->post_write_settings($original)) {
            $count++;
        }
    }

    if (empty($adminroot->errors)) {
        if ($count) {
            redirect($PAGE->url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        redirect($PAGE->url);
    }

    admin_get_root(true);
    $errormsg = get_string('errorwithsettings', 'admin');
}

// Display the page.
echo $output->header();
echo $output->heading(get_string('pluginname', 'block_motrain'));
echo $output->navigation_for_managers($manager, 'settings');

if (!empty($errormsg)) {
    echo $output->notification($errormsg);
}

echo $output->render_from_template('block_motrain/settings_page', [
    'actionurl' => $PAGE->url->out(false),
    'content' => $settingspage->output_html(),
    'sesskey' => sesskey()
]);

echo $output->footer();



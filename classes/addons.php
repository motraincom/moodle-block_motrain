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
 * Addons.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain;

use core_plugin_manager;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Addons.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addons {

    /**
     * Get the list of addons.
     *
     * @return object[] The addons.
     */
    public static function get_addons() {
        $plugins = core_plugin_manager::instance()->get_plugins_of_type('local');
        return array_map(function($plugin) {
            $hassettings = file_exists($plugin->full_path('settings.php'));
            $settingsurl = new moodle_url('/admin/settings.php', ['section' => $plugin->component]);
            if ($hassettings && component_callback_exists($plugin->component, 'get_settings_page_url')) {
                $settingsurl = component_callback($plugin->component, 'get_settings_page_url');
            }
            return (object) [
                'component' => $plugin->component,
                'displayname' => $plugin->displayname,
                'enabled' => get_config($plugin->component, 'enabled'),
                'settingsurl' => $hassettings ? $settingsurl : null,
            ];
        }, array_filter($plugins, function($plugin) {
            return strpos($plugin->name, 'motrainaddon') === 0;
        }));
    }

    /**
     * Get the list of addons with the function.
     *
     * @param string $functionname The function name.
     */
    public static function get_list_with_function($functionname) {
        return array_filter(get_plugin_list_with_function('local', $functionname), function($functionname, $pluginname) {
            return strpos($pluginname, 'local_motrainaddon') === 0
                && (bool) get_config($pluginname, 'enabled');
        }, ARRAY_FILTER_USE_BOTH);
    }

}

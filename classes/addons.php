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

use block_motrain\local\compat\admin_category;
use core_plugin_manager;
use lang_string;
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
     * Add a page to the Motrain add-ons admin category.
     *
     * The block settings tree is not loaded by Moodle for users without moodle/site:config, even when they have
     * block/motrain:manage. This ensures the add-on category exists before an add-on registers its page.
     *
     * In normal circumstances, the block settings are loaded first, so the block itself does not need to
     * validate whether the categories have already been added or not. Only addons should.
     *
     * @param \parentable_part_of_admin_tree $adminroot The admin tree root.
     * @param \part_of_admin_tree $page The admin tree page to add.
     */
    public static function add_admin_page($adminroot, $page) {
        if (!$adminroot->locate('block_motrain_category')) {
            $adminroot->add('localplugins', new admin_category('block_motrain_category', get_string('pluginname', 'block_motrain')));
        }

        if (!$adminroot->locate('block_motrain_addons')) {
            $addoncategory = new admin_category('block_motrain_addons', new lang_string('motrainaddons', 'block_motrain'));
            $adminroot->add('block_motrain_category', $addoncategory);
        }

        $adminroot->add('block_motrain_addons', $page);
    }

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

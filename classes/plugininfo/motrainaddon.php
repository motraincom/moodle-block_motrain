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
 * Plugininfo.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mootivated\plugininfo;

defined('MOODLE_INTERNAL') || die();

/**
 * Plugininfo.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class motrainaddon extends \core\plugininfo\local {

    /**
     * Return the list of enabled plugins.
     *
     * @return array
     */
    public static function get_enabled_plugins() {
        global $DB;

        // Get all available plugins.
        $plugins = \core_plugin_manager::instance()->get_installed_plugins('motrainaddon');
        if (!$plugins) {
            return [];
        }

        // Check they are enabled using get_config.
        $list = [];
        foreach ($plugins as $plugin => $version) {
            $enabled = get_config('motrainaddon_' . $plugin, 'enabled');
            if (!empty($enabled)) {
                $list[$plugin] = $plugin;
            }
        }

        return $list;
    }

    /**
     * Return URL used for management of plugins of this type.
     *
     * @return moodle_url
     */
    public static function get_manage_url() {
        return new \moodle_url('/blocks/motrain/settings_addons.php');
    }

    /**
     * Return the settings node name for this plugin.
     *
     * @return string node name
     */
    public function get_settings_section_name() {
        return 'motrainaddon_' . $this->name;
    }

}

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
 * Cli lib.
 *
 * @package    block_motrain
 * @copyright  2018 Frédéric Massart
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get the language root directory.
 *
 * @param string $component The component.
 * @return string ending with /
 */
function block_motrain_cli_get_language_root($component) {
    list($plugintype, $pluginname) = core_component::normalize_component($component);
    $root = core_component::get_plugin_directory($plugintype, $pluginname);
    if (!$root) {
        throw new coding_exception('Invalid component directory.');
    }
    return $root . '/lang/';
}

/**
 * Get the language paths.
 *
 * @param string $component The component.
 * @param string $lang The language.
 * @return Array with absolute language dir and file.
 */
function block_motrain_cli_get_language_paths($component, $lang) {
    $root = block_motrain_cli_get_language_root($component);
    $langdir = $root . $lang;
    $file = $langdir . '/' . $component . '.php';
    return [$langdir, $file];
}

/**
 * Check whether the language exists.
 *
 * @param string $lang The language.
 * @return bool
 */
function block_motrain_cli_has_language($component, $lang) {
    list($langdir, $file) = block_motrain_cli_get_language_paths($component, $lang);
    return is_dir($langdir) && is_file($file);
}

/**
 * Get the language root directory.
 *
 * @param string $component The component.
 * @return string[] ending with /
 */
function block_motrain_cli_get_languages($component) {
    $root = block_motrain_cli_get_language_root($component);
    $iter = new DirectoryIterator($root);
    $dirs = [];
    foreach ($iter as $file) {
        if (!$file->isDir()) {
            continue;
        }
        $lang = clean_param($file->getFilename(), PARAM_SAFEDIR);
        if ($lang !== $file->getFilename()) {
            continue;
        }
        $dirs[] = $lang;
    }
    return $dirs;
}

/**
 * Load the strings from the plugin.
 *
 * @param string $component The component.
 * @param string $lang The language.
 * @return array
 */
function block_motrain_cli_load_strings($component, $lang) {
    global $CFG;
    $string = [];
    list($unused, $file) = block_motrain_cli_get_language_paths($component, $lang);
    if (!is_file($file)) {
        return $string;
    }
    include($file);
    return $string;
}

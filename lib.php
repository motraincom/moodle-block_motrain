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
 * Lib.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * File serving.
 *
 * @param stdClass $course The course object.
 * @param stdClass $bi Block instance record.
 * @param context $context The context object.
 * @param string $filearea The file area.
 * @param array $args List of arguments.
 * @param bool $forcedownload Whether or not to force the download of the file.
 * @param array $options Array of options.
 * @return void|false
 */
function block_motrain_pluginfile($course, $bi, $context, $filearea, $args, $forcedownload, array $options = []) {
    $fs = get_file_storage();
    $file = null;

    if ($filearea == 'coinsimage') {
        // The coins image with public access.
        $itemid = array_shift($args);
        $filename = array_shift($args);
        $filepath = '/';
        $file = $fs->get_file($context->id, 'block_motrain', $filearea, $itemid, $filepath, $filename);
    }

    if (!$file) {
        return false;
    }

    send_stored_file($file, null, 0, true);
}

/**
 * Hook after account ID has been changed.
 */
function block_motrain_accountid_updated_hook() {
    cache_helper::purge_by_definition('block_motrain', 'metadata');
    block_motrain_check_enabled_state();
};

/**
 * Check enabled state.
 *
 * Do not declare any parameters, this function is used as a callback. It can also be
 * called multiple times in a row, for example when a few settings aresaved at once.
 */
function block_motrain_check_enabled_state() {
    $manager = manager::instance();
    $manager->check_enabled_state();
};
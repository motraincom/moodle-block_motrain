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

namespace block_motrain\local\compat;

/**
 * Admin compatibility functions.
 *
 * @package    block_motrain
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_functions {

    /**
     * Get the admin settings root.
     *
     * @param bool $reload Reload the tree.
     * @param bool $requirefulltree Require full tree.
     * @return mixed
     */
    public static function get_root(bool $reload = false, bool $requirefulltree = true) {
        if (class_exists(\core\setting\root::class)) {
            return \core\setting\root::get($reload, $requirefulltree);
        }

        return admin_get_root($reload, $requirefulltree);
    }

    /**
     * Setup an admin external page.
     *
     * @param string $identifier The page identifier.
     * @return void
     */
    public static function externalpage_setup(string $identifier): void {
        if (class_exists(\core\setting\page\externalpage::class)
            && method_exists(\core\setting\page\externalpage::class, 'setup')) {
            \core\setting\page\externalpage::setup(null, $identifier);
            return;
        }

        admin_externalpage_setup($identifier);
    }
}


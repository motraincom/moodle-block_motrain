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

// phpcs:disable Generic.Classes.DuplicateClassName.Found
// phpcs:disable PSR12.Files.FileHeader.IncorrectOrder
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

/**
 * Admin setting configselect.
 *
 * @package    block_motrain
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (class_exists(\core\setting\type\select::class)) {
    /**
     * Admin setting configselect.
     *
     * @package    block_motrain
     * © 2026 Mootivation Technologies Corp.
     * @author     Frédéric Massart <fred@branchup.tech>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    class admin_setting_configselect extends \core\setting\type\select {
    }
} else {
    /**
     * Admin setting configselect.
     *
     * @package    block_motrain
     * © 2026 Mootivation Technologies Corp.
     * @author     Frédéric Massart <fred@branchup.tech>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    class admin_setting_configselect extends \admin_setting_configselect {
    }
}


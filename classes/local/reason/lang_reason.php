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
 * Lang reason.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local\reason;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Lang reason.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lang_reason {

    /** @var object|null $args */
    protected $args;
    /** @var string $string */
    protected $string;

    /**
     * Constructor.
     *
     * @param string $string The lang string.
     * @param stdClass $args The arguments.
     */
    public function __construct($string, stdClass $args = null) {
        $this->string = $string;
        $this->args = $args;
    }

    /**
     * Get args.
     *
     * @return object|null
     */
    public function get_args() {
        return $this->args;
    }

    /**
     * Get string.
     *
     * @return string
     */
    public function get_string() {
        return $this->string;
    }

}

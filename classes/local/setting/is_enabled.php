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
 * Setting.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local\setting;

use block_motrain\manager;
use html_writer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Setting.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class is_enabled extends \admin_setting {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('block_motrain/isenabled', get_string('isenabled', 'block_motrain'), '', '');
    }

    /**
     * Always returns true.
     *
     * @return bool Always returns true.
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true.
     *
     * @return bool Always returns true.
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Never write settings.
     *
     * @param mixed $data The data.
     * @return string Always empty.
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Display.
     *
     * @param mixed $data The data.
     * @param string $query The query.
     * @return string Returns an HTML string
     */
    public function output_html($data, $query='') {
        global $PAGE;
        $manager = manager::instance();
        $isenabled = $manager->is_enabled();

        $content = html_writer::tag('strong', $isenabled ? get_string('yes', 'core') : get_string('no', 'core'));
        if ($isenabled && $manager->is_paused()) {
            $content .= ' (' . get_string('butpaused', 'block_motrain') . ')';
        }

        return format_admin_setting($this, $this->visiblename,
            $content,
            get_string('isenabled_desc', 'block_motrain'),
            false);
    }
}

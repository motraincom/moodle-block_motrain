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
 * Form.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_form extends moodleform {

    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'useridoremail', get_string('user', 'core'),
            ['placeholder' => get_string('useridoremail', 'block_motrain')]);
        $mform->setType('useridoremail', PARAM_RAW);

        foreach ($this->_customdata['hiddenfields'] ?? [] as $name => $value) {
            if ($mform->elementExists($name)) {
                continue;
            }
            $mform->addElement('hidden', $name, $value);
            $mform->setType($name, PARAM_ALPHANUMEXT);
        }

        $this->add_action_buttons(false, get_string('inspect', 'block_motrain'));
    }

}

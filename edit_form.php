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
 * Edit form.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Workaround code that would have been written in a way that does not load the form.
require_once($CFG->dirroot . '/blocks/edit_form.php');

/**
 * Edit form class.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_motrain_edit_form extends block_edit_form {

    /**
     * Form definition.
     *
     * @param MoodleQuickForm $mform Moodle form.
     * @return void
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'confighdr', get_string('appearance', 'core'));
        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_motrain'));
        $mform->setDefault('config_title', get_string('defaulttitle', 'block_motrain'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('editor', 'config_footercontent', get_string('configfootercontent', 'block_motrain'));
        $mform->addHelpButton('config_footercontent', 'configfootercontent', 'block_motrain');
    }

}

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
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\form;

use block_motrain\manager;
use context_system;
use core_collator;
use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Form.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class team_form extends moodleform {

    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general', 'core'));

        $mform->addElement('select', 'cohortid', get_string('cohort', 'block_motrain'), $this->get_cohorts());
        $mform->addHelpButton('cohortid', 'cohort', 'block_motrain');

        $mform->addElement('select', 'teamid', get_string('team', 'block_motrain'), $this->get_teams());
        $mform->addHelpButton('teamid', 'team', 'block_motrain');

        $this->add_action_buttons();
    }

    /**
     * Validation.
     *
     * @param array $data The data.
     * @param array $files The files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['cohortid'])) {
            $errors['cohortid'] = get_string('invaliddata', 'core_error');
        }
        if (empty($data['teamid'])) {
            $errors['teamid'] = get_string('invaliddata', 'core_error');
        }
        return $errors;
    }

    /**
     * Get the list of cohorts.
     *
     * @return array Keys are cohort IDs, values are their names.
     */
    protected function get_cohorts() {
        global $DB;

        $manager = manager::instance();
        $context = context_system::instance();
        $cohorts = cohort_get_cohorts($context->id, 0, 500)['cohorts'];
        $exceptids = $DB->get_records('block_motrain_team', ['accountid' => $manager->get_account_id()], '', 'cohortid');

        // We can't create an "all associations" if there are other associations.
        $data = [];
        if (empty($exceptids)) {
            $data[-1] = get_string('nocohortallusers', 'block_motrain');
        }
        $data[0] = '---';

        $cohorts = array_diff_ukey($cohorts, $exceptids, function($a, $b) {
            return $a == $b ? 0 : -1;
        });
        foreach ($cohorts as $id => $cohort) {
            $data[$id] = format_string($cohort->name, true, ['context' => $context]);
        }
        return $data;
    }

    /**
     * Get the teams.
     *
     * @return array The teams.
     */
    protected function get_teams() {
        $teams = $this->_customdata['teams'];

        $data = [0 => '---'];
        foreach ($teams as $id => $name) {
            $data[$id] = $name;
        }

        return $data;
    }

}

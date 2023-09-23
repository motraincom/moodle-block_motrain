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
 * Table.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

use block_motrain\manager;
use stdClass;
use table_sql;
use block_motrain\local\user_utils;
use confirm_action;
use html_writer;
use moodle_url;
use pix_icon;

/**
 * Table.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users_multi_teams_table extends table_sql {

    /** @var string The key of the user ID column. */
    public $useridfield = 'id';
    /** @var manager The manager. */
    protected $manager;
    /** @var renderer_base The renderer. */
    protected $renderer;

    /**
     * Constructor.
     *
     * @param manager $manager The manager.
     */
    public function __construct(manager $manager) {
        parent::__construct('block_motrain_multi_teams_users');

        global $DB, $PAGE;

        $this->manager = $manager;
        $this->renderer = $PAGE->get_renderer('block_motrain');

        $columns = [
            'id' => 'ID',
            'fullname' => get_string('fullname', 'core'),
            'email' => get_string('email', 'core'),
            'cohorts' => get_string('cohorts', 'core_cohort'),
            'actions' => '',
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        $cohortidsconcat = $DB->sql_group_concat('c.id', ',', 'c.id');
        $cohortnamesconcat = $DB->sql_group_concat('c.name', '|||', 'c.id');

        // Define SQL.
        $this->sql = new stdClass();
        $this->sql->fields = 'u.id, u.email, mt.cohortids, mt.cohortnames, ' . user_utils::name_fields('u');
        $this->sql->from = "{user} u JOIN (
                    SELECT cm.userid, $cohortidsconcat AS cohortids, $cohortnamesconcat AS cohortnames
                      FROM {cohort_members} cm
                      JOIN {cohort} c ON cm.cohortid = c.id
                      JOIN {block_motrain_teammap} t ON t.cohortid = cm.cohortid
                     WHERE t.accountid = :accountid
                  GROUP BY cm.userid
                    HAVING COUNT(t.id) > 1) mt
                        ON mt.userid = u.id";
        $this->sql->where = 'u.deleted = 0';
        $this->sql->params = ['accountid' => $manager->get_account_id()];

        // Define various table settings.
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_sorting('cohorts');
        $this->collapsible(false);
    }

    /**
     * Column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_actions($row) {
        return $this->renderer->action_icon(
            new moodle_url('/blocks/motrain/settings_players.php', ['action' => 'inspect',
                'useridoremail' => $row->id, 'sesskey' => sesskey()]),
            new pix_icon('i/info', get_string('inspect', 'block_motrain'))
        );
    }

    /**
     * Column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_cohorts($row) {
        $ids = explode(',', $row->cohortids);
        $names = explode('|||', $row->cohortnames);
        $o = '';
        foreach ($ids as $key => $id) {
            $o .= html_writer::div(html_writer::link(new moodle_url('/cohort/assign.php', ['id' => $id]), $names[$key]));
        }
        return $o;
    }

}

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
 * @copyright  2022 Mootivation Technologies Corp.
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

/**
 * Table.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class players_mapping_table extends table_sql {

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
        parent::__construct('block_motrain_playermap');

        global $PAGE;

        $this->manager = $manager;
        $this->renderer = $PAGE->get_renderer('block_motrain');

        $columns = [
            'fullname' => get_string('fullname', 'core'),
            'playerid' => get_string('playerid', 'block_motrain'),
            'blocked' => '',
            'actions' => ''
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        // Define SQL.
        $this->sql = new stdClass();
        $this->sql->fields = 'u.id, pm.playerid AS pmplayerid, pm.blocked AS pmblocked, pm.blockedreason AS pmblockedreason, '
                             . user_utils::name_fields('u');
        $this->sql->from = '{block_motrain_playermap} pm JOIN {user} u ON pm.userid = u.id';
        $this->sql->where = 'pm.accountid = :accountid';
        $this->sql->params = ['accountid' => $manager->get_account_id()];

        // Define various table settings.
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_sorting('playerid');
        $this->no_sorting('actions');
        $this->collapsible(false);
    }

    /**
     * Column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_actions($row) {
        return '';
    }

    /**
     * Column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_blocked($row) {
        if (empty($row->pmblocked)) {
            return '';
        }
        return '⚠ ' . $row->pmblockedreason;
    }

    /**
     * Column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_playerid($row) {
        if (empty($row->pmplayerid)) {
            return '-';
        }
        return \html_writer::link(
            $this->manager->get_dashboard_url('/player/' . $row->pmplayerid),
            $row->pmplayerid,
            ['target' => '_blank']
        );
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        $showfilters = false;

        if ($this->can_be_reset()) {
            $showfilters = true;
        }

        // Render button to allow user to reset table preferences, and the initial bars if some filters
        // are used. If none of the filters are used and there is nothing to display it just means that
        // the course is empty and thus we do not show anything but a message.
        echo $this->render_reset_button();
        if ($showfilters) {
            $this->print_initials_bar();
        }

        $message = get_string('nothingtodisplay', 'core');
        echo \html_writer::div(
            $this->renderer->notification($message, 'info'),
            '',
            ['style' => 'margin: 1em 0']
        );
    }

}

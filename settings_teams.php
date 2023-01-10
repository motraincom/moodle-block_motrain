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
 * Settings.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\form\team_form;
use block_motrain\manager;
use core\output\notification;

require_once(__DIR__ . '/../../config.php');

$motrainteamid = optional_param('id', null, PARAM_INT);
$deleteid = optional_param('delete', null, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

require_login();
$manager = manager::instance();
$manager->require_manage();

$PAGE->set_url('/blocks/motrain/settings_teams.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'block_motrain'));

$output = $PAGE->get_renderer('block_motrain');

if (!$manager->is_enabled()) {
    echo $output->header();
    echo $output->navigation_for_managers($manager, 'teams');
    echo $output->notification(get_string('pluginnotenabledseesettings', 'block_motrain'));
    echo $output->footer();
    die();
}

// Prepare the current URL.
$currenturl = new moodle_url($PAGE->url);
if ($motrainteamid !== null) {
    $currenturl->param('id', $motrainteamid);
}

// Handle deletion.
if ($deleteid && confirm_sesskey()) {
    $DB->delete_records('block_motrain_teammap', ['id' => $deleteid]);
    redirect($PAGE->url);
}

// Get the teams from Motrain.
$teams = $manager->get_client()->get_teams();
core_collator::asort_objects_by_property($teams, 'name', core_collator::SORT_NATURAL);
$teamsbyid = array_reduce($teams, function($carry, $item) {
    $carry[$item->id] = $item->name;
    return $carry;
}, []);

$isusingcohorts = $manager->is_using_cohorts();
$globalassociation = $manager->get_global_team_association();

// Process the form submission.
$form = new team_form($currenturl->out(false), ['isusingcohorts' => $isusingcohorts, 'globalassociation' => $globalassociation,
    'teams' => $teamsbyid, 'isedit' => (bool) $motrainteamid]);
if ($motrainteamid !== null) {

    if ($motrainteamid) {
        $association = $DB->get_record('block_motrain_teammap', ['id' => $motrainteamid], '*', MUST_EXIST);
    } else {
        $association = (object) ['accountid' => $manager->get_account_id()];
    }
    $form->set_data($association);

    if ($data = $form->get_data()) {
        if (!$isusingcohorts && $globalassociation && !$motrainteamid) {
            throw new coding_exception('cannotcreatenewassociations');
        }

        $association->cohortid = $data->cohortid;
        $association->teamid = $data->teamid;
        if (empty($association->id)) {
            $association->id = $DB->insert_record('block_motrain_teammap', $association);

            // Schedule for the users to be pushed.
            if ($manager->is_automatic_push_enabled() && $association->cohortid > 0) {
                $manager->schedule_cohort_sync($association->cohortid);
            }
        }

        // TODO We do not support updates yet.

        redirect($PAGE->url, get_string('teamassociationcreated', 'block_motrain'));

    } else if ($form->is_cancelled()) {
        redirect($PAGE->url);
    }
}

// Display the page.
echo $output->header();
echo $output->navigation_for_managers($manager, 'teams');

if ($motrainteamid !== null) {
    if ($motrainteamid) {
        echo $output->heading(get_string('editassociation', 'block_motrain'), 3);
    } else {
        echo $output->heading(get_string('createassociation', 'block_motrain'), 3);
    }
    $form->display();

} else {

    if (!$globalassociation || $isusingcohorts) {
        echo html_writer::div(
            $output->single_button(
                new moodle_url($PAGE->url, ['id' => 0]),
                get_string('createassociation', 'block_motrain'),
                'get'
            ),
            '',
            ['style' => 'margin: 0 0 1rem']
        );
    }

    $sql = "SELECT mt.*, c.name AS cohortname
              FROM {block_motrain_teammap} mt
         LEFT JOIN {cohort} c
                ON c.id = mt.cohortid
             WHERE mt.accountid = ?";
    $params = [$manager->get_account_id()];
    if ($isusingcohorts) {
        $sql .= ' AND mt.cohortid > 0';
    } else {
        $sql .= ' AND mt.cohortid < 0';
    }
    $sql .= " ORDER BY c.name";
    $records = $DB->get_records_sql($sql, $params);

    $table = new html_table();
    $table->head = [get_string('cohort', 'block_motrain'), get_string('team', 'block_motrain'), ''];
    $table->data = array_map(function($record) use ($teamsbyid, $PAGE, $output) {
        $cohortname = get_string('nocohortallusers', 'block_motrain');
        if ($record->cohortid > 0) {
            $cohortname = !empty($record->cohortname) ? $record->cohortname : '?';
            $cohortname = format_string($cohortname, true, ['context' => context_system::instance()]);
        }

        $teamname = isset($teamsbyid[$record->teamid]) ? $teamsbyid[$record->teamid] : $record->teamid;
        return [
            $cohortname,
            // html_writer::link(new moodle_url($PAGE->url, ['id' => $record->id]), $cohortname),
            html_writer::span($teamname, '', ['title' => $record->teamid]),
            $output->action_icon(new moodle_url($PAGE->url, ['delete' => $record->id, 'sesskey' => sesskey()]),
                new pix_icon('i/delete', get_string('delete')), new confirm_action(get_string('areyousure', 'core')))
        ];
    }, $records);

    if (!empty($records)) {
        echo html_writer::table($table);
    } else {
        echo $output->notification(get_string('noteamsyetcreatefirst', 'block_motrain'), notification::NOTIFY_INFO);
    }
}

echo $output->footer();

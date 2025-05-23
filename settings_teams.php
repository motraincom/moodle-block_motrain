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
$refreshid = optional_param('refresh', null, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

require_login();
$manager = manager::instance();
$manager->require_manage();

$PAGE->set_url('/blocks/motrain/settings_teams.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('teamassociations', 'block_motrain'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('block_motrain');

if (!$manager->is_enabled()) {
    echo $output->header();
    echo $output->heading(get_string('pluginname', 'block_motrain'));
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

// Handle refresh.
if ($refreshid && confirm_sesskey()) {
    $teammap = $DB->get_record('block_motrain_teammap', ['id' => $refreshid], '*', MUST_EXIST);
    $manager->get_player_mapper()->remove_team($teammap->teamid);
    if ($manager->is_automatic_push_enabled() && $teammap->cohortid > 0) {
        $manager->schedule_cohort_sync($teammap->cohortid);
    }
    redirect($PAGE->url);
}

// Handle deletion.
if ($deleteid && confirm_sesskey()) {
    $teamid = $DB->get_field('block_motrain_teammap', 'teamid', ['id' => $deleteid]) ?: '';
    $manager->get_player_mapper()->remove_team($teamid);
    $DB->delete_records('block_motrain_teammap', ['id' => $deleteid]);
    redirect($PAGE->url);
}

// Get the teams from Incentli.
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

        redirect($PAGE->url, get_string('teamassociationcreated', 'block_motrain'));

    } else if ($form->is_cancelled()) {
        redirect($PAGE->url);
    }
}

// Display the page.
echo $output->header();
echo $output->heading(get_string('pluginname', 'block_motrain'));
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

    if ($isusingcohorts) {
        $mixedteamsusers = $manager->get_team_resolver()->count_users_in_multiple_teams();
        if ($mixedteamsusers > 0) {
            $inspectlink = html_writer::link(new moodle_url('/blocks/motrain/settings_teams_mismatch.php'),
                get_string('viewlist', 'block_motrain'));
            $notif = new notification(get_string('therearexusersinmultipleteams', 'block_motrain', $mixedteamsusers)
                . ' ' . $inspectlink, notification::NOTIFY_WARNING, false);
            echo $output->render($notif);
        }
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
    $table->head = [get_string('cohort', 'block_motrain'), get_string('team', 'block_motrain'), '', ''];
    $table->data = array_map(function($record) use ($teamsbyid, $PAGE, $output, $manager) {
        $cohortname = get_string('nocohortallusers', 'block_motrain');
        $cohorturl = null;
        $refreshmsg = 'areyousurerefreshallinteam';
        if ($record->cohortid > 0) {
            $cohortname = !empty($record->cohortname) ? $record->cohortname : '?';
            $cohortname = format_string($cohortname, true, ['context' => context_system::instance()]);
            $cohorturl = new moodle_url('/cohort/assign.php', ['id' => $record->cohortid]);
            if ($manager->is_automatic_push_enabled()) {
                $refreshmsg = 'areyousurerefreshandpushallinteam';
            }
        }
        $teamname = isset($teamsbyid[$record->teamid]) ? $teamsbyid[$record->teamid] : $record->teamid;
        return [
            $cohorturl ? html_writer::link($cohorturl, $cohortname) : $cohortname,
            // html_writer::link(new moodle_url($PAGE->url, ['id' => $record->id]), $cohortname),
            html_writer::span(s($teamname), '', ['title' => $record->teamid]),
            s($record->teamid),
            $output->action_icon(new moodle_url($PAGE->url, ['refresh' => $record->id, 'sesskey' => sesskey()]),
                new pix_icon('i/reload', get_string('refresh', 'block_motrain')),
                new confirm_action(get_string($refreshmsg, 'block_motrain'))),
            $output->action_icon(new moodle_url($PAGE->url, ['delete' => $record->id, 'sesskey' => sesskey()]),
                new pix_icon('i/delete', get_string('delete')), new confirm_action(get_string('areyousure', 'core'))),
        ];
    }, $records);

    if (!empty($records)) {
        echo html_writer::table($table);
    } else {
        echo $output->notification(get_string('noteamsyetcreatefirst', 'block_motrain'), notification::NOTIFY_INFO);
    }
}

echo $output->footer();

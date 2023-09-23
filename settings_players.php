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

use block_motrain\form\user_form;
use block_motrain\manager;
use block_motrain\output\players_mapping_table;

require_once(__DIR__ . '/../../config.php');

$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$userid = optional_param('userid', null, PARAM_ALPHANUMEXT);
$useridoremail = optional_param('useridoremail', null, PARAM_RAW);

require_login();
$manager = manager::instance();
$manager->require_manage();

$baseurl = new moodle_url('/blocks/motrain/settings_players.php');
$pageurl = new moodle_url($baseurl, ['action' => $action]);

$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('playersmapping', 'block_motrain'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('block_motrain');

if (!$manager->is_enabled()) {
    echo $output->header();
    echo $output->heading(get_string('pluginname', 'block_motrain'));
    echo $output->navigation_for_managers($manager, 'players');
    echo $output->notification(get_string('pluginnotenabledseesettings', 'block_motrain'));
    echo $output->footer();
    die();
}

$teamresolver = $manager->get_team_resolver();
$playermap = $manager->get_player_mapper();

if ($action === 'delete' && confirm_sesskey()) {
    $playermap->remove_user($userid);
    redirect($baseurl);
} else if ($action === 'reset' && confirm_sesskey()) {
    $playermap->unblock_user($userid);
    redirect($baseurl);
}

// Display the page.
echo $output->header();
echo $output->heading(get_string('pluginname', 'block_motrain'));
echo $output->navigation_for_managers($manager, 'players');

$validflag = $output->pix_icon('i/valid', '✅') . '<span class="sr-only">✅</span>';
$invalidflag = $output->pix_icon('i/invalid', '❌') . '<span class="sr-only">❌</span>';

if ($action === 'inspect') {
    echo $output->render_from_template('block_motrain/heading', [
        'backurl' => $baseurl->out(false),
        'title' => get_string('inspectuser', 'block_motrain'),
    ]);

    $form = new user_form($pageurl->out(false), ['hiddenfields' => $PAGE->url->params()], 'get');
    $form->set_data(['useridoremail' => $useridoremail]);
    $form->display();

    $localuser = null;
    if ($useridoremail) {
        $userid = (int) $useridoremail;
        $candidateemail = (string) $useridoremail;
        if ($userid) {
            $localuser = core_user::get_user($userid);
        } else if (strpos($candidateemail, '@') !== false) {
            $localuser = core_user::get_user_by_email($candidateemail);
        }

        if (!$localuser || $localuser->deleted) {
            echo $OUTPUT->notification(get_string('userdoesnotexist', 'block_motrain'));
        } else {
            $table = new html_table();
            $table->data[] = ['ID', $localuser->id];
            $table->data[] = [get_string('fullname', 'core'), s(fullname($localuser))];
            $table->data[] = [get_string('email', 'core'), s($localuser->email)];

            // Resolve the potential teams.
            $teamid = $teamresolver->get_team_id_for_user($userid);
            $team = null;
            $otherteams = [];
            foreach ($teamresolver->get_team_candidates_for_user($userid) as $candidate) {
                if ($candidate->team_id == $teamid) {
                    $team = $candidate;
                    continue;
                }
                $otherteams[] = $candidate;
            }
            $outputteamflag = function($otherteamid) use ($teamid, $validflag, $invalidflag) {
                if (!$teamid || !$otherteamid) {
                    return '';
                }
                return ' ' . ($otherteamid == $teamid ? $validflag : $invalidflag);
            };

            // Output the primary and secondary teams.
            if ($team) {
                $table->data[] = [
                    get_string('primaryteam', 'block_motrain'),
                    html_writer::div(
                        html_writer::div(s($team->team_id)) .
                        html_writer::div(get_string('sourcex', 'block_motrain', s($team->local_name)))
                    )
                ];
                foreach ($otherteams as $i => $otherteam) {
                    $n = $i + 1;
                    $table->data[] = [
                        get_string('secondaryteam', 'block_motrain', $n),
                        html_writer::div(
                            html_writer::div(s($otherteam->team_id) . '‼️') .
                            html_writer::div(get_string('sourcex', 'block_motrain', s($otherteam->local_name)))
                        )
                    ];
                }
            } else {
                $table->data[] = [get_string('primaryteam', 'block_motrain'), '-'];
            }
            echo $OUTPUT->heading(get_string('local', 'core'), 4);
            echo html_writer::table($table);

            // Retrieve the local mapping.
            $playermap->set_local_only(true);
            $playermapping = $playermap->get_player_mapping($userid);
            $playerid = $playermapping->playerid ?? null;
            $table = new html_table();
            $table->data[] = [get_string('accountid', 'block_motrain'), $manager->get_account_id() ?: '-'];
            $table->data[] = [get_string('playerid', 'block_motrain'), $playerid ?? '-'];
            $table->data[] = [get_string('team', 'block_motrain'), ($playermapping->teamid ?? '-')
                . $outputteamflag($playermapping->teamid ?? null)];
            if ($playermapping->blocked ?? false) {
                $table->data[] = [get_string('blocked', 'block_motrain'), get_string('yes', 'core') . '. '
                    . s($playermapping->blockedreason ?? '-')];
            } else {
                $table->data[] = [get_string('blocked', 'block_motrain'), get_string('no', 'core')];
            }
            echo $OUTPUT->heading(get_string('playermapping', 'block_motrain'), 4);
            echo html_writer::table($table);

            // Retrieve remote information by player ID.
            $table = new html_table();
            if ($playerid) {
                echo $OUTPUT->heading(get_string('motrainidlookup', 'block_motrain'), 4);
                $table->data[] = ['ID', s($playerid)];
                try {
                    $remoteplayer = $manager->get_client()->get_player($playerid);
                    if ($remoteplayer) {
                        $playeremailflag = '';
                        if ($localuser->email != $remoteplayer->email) {
                            $playeremailflag = $invalidflag;
                        } else if ($localuser->email == $remoteplayer->email) {
                            $playeremailflag = $validflag;
                        }
                        $table->data[] = [get_string('name', 'core'), s($remoteplayer->firstname)
                            . ' ' . s($remoteplayer->lastname)];
                        $table->data[] = [get_string('email', 'core'), s($remoteplayer->email) . ' ' . $playeremailflag];
                        $table->data[] = [get_string('team', 'block_motrain'), s($remoteplayer->team_id)
                            . $outputteamflag($remoteplayer->team_id)];
                    } else {
                        $table->data[] = [get_string('result', 'block_motrain'), get_string('notfound', 'block_motrain')];
                    }
                } catch (\moodle_exception $e) {
                    $remoteplayer = null;
                    $table->data[] = [get_string('error', 'core'), $e->getMessage()];
                }
                echo html_writer::table($table);
            }

            // Retrieve remote information by email.
            echo $OUTPUT->heading(get_string('motrainemaillookup', 'block_motrain'), 4);
            $table = new html_table();
            $table->data[] = [get_string('email', 'core'), s($localuser->email)];
            try {
                $remoteplayer = $manager->get_client()->get_player_by_email_in_account($localuser->email);
                if ($remoteplayer) {
                    $playeridflag = '';
                    if ($playerid && $remoteplayer->id != $playerid) {
                        $playeridflag = $invalidflag;
                    } else if ($playerid && $remoteplayer->id == $playerid) {
                        $playeridflag = $validflag;
                    }
                    $table->data[] = ['ID', s($remoteplayer->id) . ' ' . $playeridflag];
                    $table->data[] = [get_string('name', 'core'), s($remoteplayer->firstname)
                        . ' ' . s($remoteplayer->lastname)];
                    $table->data[] = [get_string('team', 'block_motrain'), s($remoteplayer->team_id)
                        . $outputteamflag($remoteplayer->team_id)];
                } else {
                    $table->data[] = [get_string('result', 'block_motrain'), get_string('notfound', 'block_motrain')];
                }
            } catch (\moodle_exception $e) {
                $remoteplayer = null;
                $table->data[] = [get_string('error', 'core'), $e->getMessage()];
            }
            echo html_writer::table($table);
        }
    }

} else {
    echo html_writer::start_div('', ['style' => 'display: flex']);
    echo html_writer::start_div();
    echo html_writer::tag('p', get_string('playermappingintro', 'block_motrain'));
    echo html_writer::end_div();
    echo html_writer::start_div('', ['style' => 'flex: 1 0 auto']);
    echo $output->single_button(
        new moodle_url($baseurl, ['action' => 'inspect']),
        get_string('inspectuser', 'block_motrain'),
        'get'
    );
    echo html_writer::end_div();
    echo html_writer::end_div();

    $table = new players_mapping_table($manager);
    $table->define_baseurl($pageurl);
    $table->out(50, true);
}

echo $output->footer();

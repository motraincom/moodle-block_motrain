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
 * Redirect.
 *
 * This page serves as transition to avoid long requests and
 * many HTTP header redirects that leave the user waiting for long on
 * the same page as the initial one.
 *
 * Here we display something, and immediately redirect to the next URL.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\local\api_error;
use block_motrain\manager;

require(__DIR__ . '/../../config.php');

require_login(null, false);
require_sesskey();

$landingpage = optional_param('page', null, PARAM_ALPHANUMEXT);
$do = optional_param('do', 0, PARAM_INT);
$retry = optional_param('retry', 0, PARAM_INT);

$pleasewait = get_string('pleasewait', 'block_motrain');

$PAGE->set_url('/blocks/motrain/iframe.php', ['page' => $landingpage]);
$PAGE->set_pagelayout('embedded');
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title($pleasewait);
$PAGE->navigation->override_active_url(new moodle_url('/user/profile.php', ['id' => $USER->id]));

if (!$do) {
    echo $OUTPUT->header();
    $icon = $OUTPUT->pix_icon('i/loading', '');
    $goto = new moodle_url($PAGE->url, ['do' => 1, 'sesskey' => sesskey()]);
    $urlencoded = $goto->out(false);
    echo html_writer::tag('div', "{$icon} {$pleasewait}", ['style' => 'margin: 1em;']);
    echo html_writer::script("window.location.href = '$urlencoded';");
    echo $OUTPUT->footer();
    die();
}

try {
    $manager = manager::instance();
    $manager->require_enabled();
    $manager->require_not_paused();
    $manager->require_view();

    $teamid = $manager->get_team_resolver()->get_team_id_for_user($USER->id);
    if (!$teamid) {
        throw new \moodle_exception('userteamnotfound', 'block_motrain');
    }
    $playerid = $manager->get_player_mapper()->get_player_id($USER, $teamid);
    if (!$playerid) {
        throw new \moodle_exception('playeridnotfound', 'block_motrain');
    }

    $client = $manager->get_client();

    try {
        $url = $client->get_store_login_url($playerid, $landingpage);
    } catch (api_error $e) {
        // It would appear that the player is not found. That is likely because we have a mapping that
        // is no tied to a player that has been deleted. In this case, we delete the mapping and retry.
        if ($e->get_http_code() == 404 && $playerid && !$retry) {
            $manager->get_player_mapper()->remove_user($USER->id);
            $retryurl = new moodle_url($PAGE->url, ['retry' => 1, 'sesskey' => sesskey()]);
            redirect($retryurl);
        }
        throw $e;
    }
} catch (moodle_exception $e) {
    $PAGE->set_pagelayout('standard');
    throw $e;
}

// Invalid the balance when loading the iframe.
$manager->get_balance_proxy()->invalidate_balance($USER->id);

redirect($url);

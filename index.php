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
 * Embedded experience.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\manager;

require(__DIR__ . '/../../config.php');

require_login(null, false);

$landingpage = optional_param('page', null, PARAM_ALPHANUMEXT);

$PAGE->set_url('/blocks/motrain/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title(get_string('store', 'block_motrain'));
$PAGE->set_heading(get_string('store', 'block_motrain'));
$PAGE->navigation->override_active_url(new moodle_url('/user/profile.php', ['id' => $USER->id]));
$PAGE->navbar->add(get_string('store', 'block_motrain'));

$manager = manager::instance();
$manager->require_enabled();
$manager->require_view();

$iframeurl = new moodle_url('/blocks/motrain/iframe.php', [
    'sesskey' => sesskey(),
    'page' => $landingpage,
]);

echo $OUTPUT->header();

$teamid = $manager->get_team_resolver()->get_team_id_for_user($USER->id);
if (!$teamid) {
    throw new \moodle_exception('userteamnotfound', 'block_motrain');

} else {
    echo html_writer::tag('iframe', '', ['src' => $iframeurl->out(false),
        'style' => 'width: 100%; min-height: 800px; height: 100%; max-height: 100vh; border: none',
    ]);
}

echo $OUTPUT->footer();

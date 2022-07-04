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
 * Admin actions.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . ' /../../config.php');

$action = required_param('action', PARAM_LOCALURL);
$returnurl = required_param('returnurl', PARAM_LOCALURL);

require_login();
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

$PAGE->set_context(context_system::instance());

$message = '';
if ($action === 'purgemetadata') {
    cache_helper::purge_by_definition('block_motrain', 'metadata');
    $message = get_string('purgedefinitionsuccess', 'core_cache');
}

redirect(new moodle_url($returnurl), $message);

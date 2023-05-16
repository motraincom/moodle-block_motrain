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

use block_motrain\manager;
use core\notification;

require_once(__DIR__ . ' /../../config.php');

$action = required_param('action', PARAM_LOCALURL);
$returnurl = required_param('returnurl', PARAM_LOCALURL);

require_login();
require_sesskey();

$manager = manager::instance();
$manager->require_manage();

$PAGE->set_context(context_system::instance());

$message = '';
if ($action === 'purgemetadata') {
    cache_helper::purge_by_definition('block_motrain', 'metadata');
    $message = get_string('cachepurged', 'block_motrain');

} else if ($action === 'connectwebhook') {
    $manager->setup_webhook();
    notification::add(get_string('webhooksconnected', 'block_motrain'), notification::SUCCESS);

} else if ($action === 'disconnectwebhook') {
    $manager->unset_webhook();
    notification::add(get_string('webhooksdisconnected', 'block_motrain'), notification::SUCCESS);

    if ($manager->is_sending_local_notifications_enabled()) {
        set_config('sendlocalnotifications', 0, 'block_motrain');
        notification::add(get_string('sendlocalnotificationsdisabledwithwebhooks', 'block_motrain'), notification::WARNING);
    }
}

redirect(new moodle_url($returnurl), $message);

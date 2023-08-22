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
 * Task.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\task;
defined('MOODLE_INTERNAL') || die();

use block_motrain\manager;

/**
 * Task.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_update_webhook extends \core\task\adhoc_task {

    /**
     * Execute.
     *
     * @return void
     */
    public function execute() {

        $manager = manager::instance();
        if (!$manager->is_enabled()) {
            mtrace('Motrain is not enabled.');
            return;
        }

        if (!$manager->is_webhook_connected()) {
            mtrace('Webhooks are not connected.');
            return;
        }

        mtrace('Updating existing webhook.');
        try {
            $manager->setup_webhook();
        } catch (\moodle_exception $e) {
            mtrace('Update of existing webhook failed.');
        }
    }

}

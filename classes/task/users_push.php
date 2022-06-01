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
 * @copyright  2022 Mootivation Technologies Corp.
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
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users_push extends \core\task\scheduled_task {

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
        } else if ($manager->is_paused()) {
            mtrace('Motrain is paused.');
            return;
        }

        $userpusher = $manager->get_user_pusher();
        $queuesize = $userpusher->count_queue();
        if (!$queuesize) {
            mtrace('Push users queue is empty.');
            return;
        }

        mtrace('Pushing chunk of ' . $userpusher->get_chunk_size() . ' out of ' . $queuesize . ' users.');
        $userpusher->push_chunk();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskpushusers', 'block_motrain');
    }

}

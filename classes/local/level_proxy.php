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
 * Level proxy.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use block_motrain\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Level proxy.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class level_proxy {

    /** @var manager The manager. */
    protected $manager;

    /**
     * Constructor.
     *
     * @param manager The manager.
     */
    public function __construct(manager $manager) {
        $this->manager = $manager;
    }

    /**
     * Get the level of a user.
     *
     * @param object|int The user, or its ID.
     * @return object|null When
     */
    public function get_level($userorid) {
        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }
        $userid = (int) $userid;

        // Not cache implementation as we know the underlying data is cached.
        $coins = $this->manager->get_balance_proxy()->get_total_earned($userid);
        $levels = $this->manager->get_metadata_reader()->get_levels();

        $level = null;
        $nextlevel = null;
        foreach ($levels as $candidate) {
            if ($candidate->coins > $coins) {
                $nextlevel = $candidate;
                break;
            }
            $level = $candidate;
        }

        if ($level !== null) {
            $level->coins_in_level = max(0, $coins - $level->coins);
            $level->coins_needed = $nextlevel ? max(0, $nextlevel->coins - $level->coins) : 0;
            $level->coins_remaining = $nextlevel ? $nextlevel->coins - $coins : 0;
            $level->progress_ratio = $level->coins_needed ? $level->coins_in_level / $level->coins_needed : 1;
            $level->progress_pc = ($level->progress_ratio > 0 ? max(0.01, $level->progress_ratio) : 0) * 100;
        }

        return $level;
    }

}

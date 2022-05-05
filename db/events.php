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
 * Observers.
 *
 * @package   block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '*',
        'callback' => 'block_motrain\\observer::catch_all',
        'internal' => false
    ],
    [
        'eventname' => '\\core\\event\\cohort_deleted',
        'callback' => 'block_motrain\\observer::cohort_deleted',
    ],
    [
        'eventname' => '\\core\\event\\cohort_member_added',
        'callback' => 'block_motrain\\observer::cohort_member_added',
    ],
    [
        'eventname' => '\\core\\event\\cohort_member_removed',
        'callback' => 'block_motrain\\observer::cohort_member_removed',
    ],
    [
        'eventname' => '\\core\\event\\role_assigned',
        'callback' => 'block_motrain\\observer::role_assigned',
    ],
    [
        'eventname' => '\\core\\event\\user_created',
        'callback' => 'block_motrain\\observer::user_created',
    ],
    [
        'eventname' => '\\totara_cohort\\event\\members_updated',
        'callback' => 'block_motrain\\observer::totara_cohort_members_updated',
    ],
];

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
 * External services.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_motrain_award_coins' => [
        'classname'     => 'block_motrain\\external',
        'methodname'    => 'award_coins',
        'description'   => 'Award coins to a user.',
        'type'          => 'write',
        'capabilities'  => 'block/motrain:awardcoins',
    ],
    'block_motrain_get_activities_with_completion' => [
        'classname' => 'block_motrain\external',
        'methodname' => 'get_activities_with_completion',
        'description' => 'List the activities with completion enabled.',
        'type' => 'read',
        'ajax' => true
    ],
    'block_motrain_save_completion_rules' => [
        'classname' => 'block_motrain\external',
        'methodname' => 'save_completion_rules',
        'description' => 'Save the completion rules.',
        'type' => 'write',
        'ajax' => true
    ],
    'block_motrain_save_program_rules' => [
        'classname' => 'block_motrain\external',
        'methodname' => 'save_program_rules',
        'description' => 'Save the program rules.',
        'type' => 'write',
        'ajax' => true
    ],
];

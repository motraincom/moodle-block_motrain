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
 * Collection strategy.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use block_motrain\manager;
use context;
use core\event\course_completed;
use core\event\course_module_completion_updated;
use local_mootivated\local\lang_reason;

defined('MOODLE_INTERNAL') || die();


/**
 * Collection strategy.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collection_strategy {

    /** @var bool Whether admins can earn. */
    protected $adminscanearn = false;
    /** @var array Allowed contexts. */
    protected $allowedcontexts = [CONTEXT_COURSE, CONTEXT_MODULE];
    /** @var completion_coins_calculator The calculator. */
    protected $completioncoinscalculator;
    /** @var player_mapper The player mapper. */
    protected $playermapper;
    /** @var team_resolver The team resolver. */
    protected $teamresolver;
    /** @var array Ignore modules. */
    protected $ignoredmodules = ['local_mootivated', 'block_mootivated', 'block_motrain'];

    /**
     * Constructor.
     */
    public function __construct($teamresolver, $playermapper) {
        $this->adminscanearn = (bool) get_config('block_motrain', 'adminscanearn');
        $this->completioncoinscalculator = new completion_coins_calculator();
        $this->teamresolver = $teamresolver;
        $this->playermapper = $playermapper;
    }

    /**
     * Catch all.
     *
     * @param \core\event\base $event The event.
     */
    public function collect_event(\core\event\base $event) {
        if (!manager::instance()->is_setup()) {
            return;
        }

        if (in_array($event->component, $this->ignoredmodules)) {
            // Skip own events.
            return;
        } else if ($event->anonymous) {
            // Skip all the events marked as anonymous.
            return;
        } else if (!in_array($event->contextlevel, $this->allowedcontexts)) {
            // Ignore events that are not in the right context.
            return;
        } else if ($event->is_restored()) {
            // Ignore events that are restored.
            return;
        } else if (!$event->get_context()) {
            // Sometimes the context does not exist, not sure when...
            return;
        }

        // We only handle two events at the moment.
        if (!($event instanceof course_module_completion_updated) && !($event instanceof course_completed)) {
            return;
        }

        // Check target.
        $userid = $this->get_event_target_user($event);

        // Check if can earn coins.
        if (!$this->can_user_earn_coins($userid, $event->get_context())) {
            return;
        }

        // Resolve the team.
        $teamid = $this->teamresolver->get_team_id_for_user($userid);
        if (!$teamid) {
            return;
        }

        $coins = 1;
        if ($event instanceof course_module_completion_updated) {

            // The user may have completed an activity.
            $data = $event->get_record_snapshot('course_modules_completion', $event->objectid);
            if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) {
                $courseid = $event->courseid;
                $cmid = $event->get_context()->instanceid;

                // $school = self::get_school_resolver()->get_by_member($userid);
                // if ($school->was_user_rewarded_for_completion($userid, $courseid, $cmid)) {
                //     return;
                // }

                // $modinfo = course_modinfo::instance($courseid);
                // $cminfo = $modinfo->get_cm($cmid);
                // $calculator = $school->get_completion_points_calculator_by_mod();
                $coins = $this->completioncoinscalculator->get_module_coins($courseid, $cmid);
                // $coins = (int) $calculator->get_for_module($cminfo->modname);

                // $school->capture_event($userid, $event, $coins);
                // $school->log_user_was_rewarded_for_completion($userid, $courseid, $cmid, $data->completionstate);
            }

        } else if ($event instanceof course_completed) {
            // // Check their school.
            // $school = self::get_school_resolver()->get_by_member($userid);
            // if (!$school || !$school->is_setup()) {
            //     // No school, no chocolate.
            //     return;
            // }

            // if (!$school->is_course_completion_reward_enabled()) {
            //     // Sorry mate, no pocket money for you.
            //     return;
            // }

            // if ($school->was_user_rewarded_for_completion($userid, $event->courseid, 0)) {
            //     // The course completion state must have been reset. If we do not ignore this
            //     // then we will have issue when logging the event due to unique indexes.
            //     return;
            // }

            // // Ok, here you can have some coins.
            $coins = $this->completioncoinscalculator->get_course_coins($event->courseid);
            // $school->capture_event($userid, $event, (int) $school->get_course_completion_reward());
            // $school->log_user_was_rewarded_for_completion($userid, $event->courseid, 0, COMPLETION_COMPLETE);
        }

        return $this->award_coins($teamid, $userid, $coins);
    }

    /**
     * Award coins.
     *
     * @param string $teamid The team ID.
     * @param int $userid The user ID.
     * @param int $coins The number of coins.
     */
    protected function award_coins($teamid, $userid, $coins) {

        // Safety check.
        if ($coins <= 0) {
            return;
        }

        // TODO Save failures.
        $playerid = $this->playermapper->get_player_id($userid, $teamid);
        if (!$playerid) {
            return;
        }

        $this->client->add_coins($playerid, $coins, new lang_reason('transaction:credit.activityxcompleted', (object) [
            'name' => 'Test'
        ]));

        // TODO Save awards.
    }

    /**
     * Can the user earn.
     *
     * Note that this is unlikely to return true when the context is not a course context
     * because students are given the capability to earn coins, not the mootivated user role.
     *
     * @param int $userid The user ID.
     * @param context $context The context.
     * @return bool
     */
    protected function can_user_earn_coins($userid, context $context) {

        // If non-logged in users, guests or admin, deny.
        if (!$userid || isguestuser($userid) || (!$this->adminscanearn && is_siteadmin($userid))) {
            return false;
        }

        // Check has capability in context.
        if (!has_capability('block/motrain:earncoins', $context, $userid)) {
            return false;
        }

        return true;
    }

    /**
     * Get the target of an event.
     *
     * @param \core\base\event $event The event.
     * @return int The user ID.
     */
    protected function get_event_target_user(\core\event\base $event) {
        $userid = $event->userid;
        if ($event instanceof \core\event\course_completed || $event instanceof \core\event\course_module_completion_updated) {
            $userid = $event->relateduserid;
        }
        return $userid;
    }

}

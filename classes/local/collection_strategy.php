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

use block_motrain\client;
use block_motrain\local\award\award;
use block_motrain\local\reason\lang_reason;
use block_motrain\manager;
use context;
use core\event\course_completed;
use core\event\course_module_completion_updated;
use moodle_exception;

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

    /** @var array Allowed contexts. */
    protected $allowedcontexts = [CONTEXT_COURSE, CONTEXT_MODULE];
    /** @var completion_coins_calculator The calculator. */
    protected $completioncoinscalculator;
    /** @var manager The manager. */
    protected $manager;
    /** @var array Ignore modules. */
    protected $ignoredmodules = ['local_mootivated', 'block_mootivated', 'block_motrain'];

    /**
     * Constructor.
     */
    public function __construct(manager $manager) {
        $this->completioncoinscalculator = new completion_coins_calculator();
        $this->manager = $manager;
    }

    /**
     * Catch all.
     *
     * @param \core\event\base $event The event.
     */
    public function collect_event(\core\event\base $event) {
        global $DB;

        if (!$this->manager->is_enabled()) {
            return;
        } else if ($this->manager->is_paused()) {
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

        $context = $event->get_context();

        // We only handle two events at the moment.
        if (!($event instanceof course_module_completion_updated) && !($event instanceof course_completed)) {
            return;
        }

        // Check target.
        $userid = $this->get_event_target_user($event);

        // Check if can earn coins.
        if (!$this->can_user_earn_coins($userid, $context)) {
            return;
        }

        if ($event instanceof course_module_completion_updated) {

            // The user may have completed an activity.
            $data = $event->get_record_snapshot('course_modules_completion', $event->objectid);
            if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) {
                $courseid = $event->courseid;
                $cmid = $context->instanceid;

                $award = new award($userid, $context->id, 'cm_completed', sha1($cmid));
                if ($award->has_been_recorded_previously()) {
                    return;
                }

                $coins = $this->completioncoinscalculator->get_module_coins($courseid, $cmid);
                if ($coins <= 0) {
                    return;
                }

                try {
                    $modinfo = get_fast_modinfo($courseid);
                    $cminfo = $modinfo->get_cm($cmid);
                    $cmname = format_string($cminfo->name, true, ['context' => $context]);
                    $reasonstr = 'transaction:credit.activityxcompleted';
                    $reasonargs = (object) ['name' => $cmname];
                } catch (\moodle_exception $e) {
                    $reasonstr = null;
                    $reasonargs = null;
                }
                if (empty($reasonstr)) {
                    $reasonstr = 'transaction:credit.activitycompleted';
                    $reasonargs = null;
                }
                $award->give($coins, new lang_reason($reasonstr, $reasonargs));
            }

        } else if ($event instanceof course_completed) {
            $courseid = $event->courseid;

            $award = new award($userid, $context->id, 'course_completed', sha1($courseid));
            if ($award->has_been_recorded_previously()) {
                return;
            }

            $coins = $this->completioncoinscalculator->get_course_coins($courseid);
            if ($coins <= 0) {
                return;
            }

            try {
                $modinfo = get_fast_modinfo($courseid);
                $coursename = format_string($modinfo->get_course()->fullname, true, ['context' => $context]);
                $reasonstr = 'transaction:credit.coursexcompleted';
                $reasonargs = (object) ['name' => $coursename];
            } catch (\moodle_exception $e) {
                $reasonstr = null;
                $reasonargs = null;
            }
            if (empty($reasonstr)) {
                $reasonstr = 'transaction:credit.coursecompleted';
                $reasonargs = null;
            }

            $award->give($coins, new lang_reason($reasonstr, $reasonargs));
        }
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
        return $this->manager->can_earn_in_context($userid, $context);
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

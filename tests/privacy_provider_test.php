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
 * Test privacy provider.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;
use core_privacy\tests\request\approved_contextlist;
use block_motrain\privacy\provider;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;

/**
 * Privacy provider testcase.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_motrain_privacy_provider_testcase extends \advanced_testcase {

    public function test_get_metadata() {
        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped("Privacy providers not installed");
        }

        $data = provider::get_metadata(new collection('block_motrain'));
        $this->assertCount(5, $data->get_collection());
    }

    public function test_get_contexts_for_userid() {
        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped("Privacy providers not installed");
        }
        $this->resetAfterTest(true);
        extract($this->generate_test_data());

        $contextlist = provider::get_contexts_for_userid($u1->id);
        $this->assert_contextlist_equals($contextlist, [SYSCONTEXTID, context_course::instance($c1->id)->id,
            context_course::instance($c2->id)->id]);
        $contextlist = provider::get_contexts_for_userid($u2->id);
        $this->assert_contextlist_equals($contextlist, [SYSCONTEXTID, context_course::instance($c1->id)->id]);
        $contextlist = provider::get_contexts_for_userid($u3->id);
        $this->assert_contextlist_equals($contextlist, [SYSCONTEXTID]);
        $contextlist = provider::get_contexts_for_userid($u4->id);
        $this->assert_contextlist_equals($contextlist, []);
    }

    public function test_get_users_in_context() {
        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped("Privacy providers not installed");
        } else if (!interface_exists('core_privacy\local\request\core_userlist_provider')) {
            $this->markTestSkipped("Interface core_userlist_provider not available");
        }

        $this->resetAfterTest(true);
        extract($this->generate_test_data());

        $sysctx = context_system::instance();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $userlist = new userlist($sysctx, 'block_motrain');
        provider::get_users_in_context($userlist);
        $this->assert_userlist_contains_userids($userlist, [$u1->id, $u2->id, $u3->id]);

        $userlist = new userlist($c1ctx, 'block_motrain');
        provider::get_users_in_context($userlist);
        $this->assert_userlist_contains_userids($userlist, [$u1->id, $u2->id]);

        $userlist = new userlist($c2ctx, 'block_motrain');
        provider::get_users_in_context($userlist);
        $this->assert_userlist_contains_userids($userlist, [$u1->id]);
    }

    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped("Privacy providers not installed");
        }
        $this->resetAfterTest(true);
        extract($this->generate_test_data());

        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $this->assertTrue($DB->record_exists('block_motrain_log', []));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['contextid' => $c2ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', []));

        provider::delete_data_for_all_users_in_context(context_course::instance($c1->id));

        $this->assertTrue($DB->record_exists('block_motrain_log', []));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['contextid' => $c2ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', []));

        provider::delete_data_for_all_users_in_context(context_system::instance());

        $this->assertTrue($DB->record_exists('block_motrain_log', []));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['contextid' => $c2ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_playermap', []));
    }

    public function test_delete_data_for_user() {
        global $DB;

        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped("Privacy providers not installed");
        }
        $this->resetAfterTest(true);
        extract($this->generate_test_data());

        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));

        $approvedcontexts = new approved_contextlist($u1, 'block_motrain', [$c1ctx->id]);
        provider::delete_data_for_user($approvedcontexts);

        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));

        $approvedcontexts = new approved_contextlist($u2, 'block_motrain', [SYSCONTEXTID]);
        provider::delete_data_for_user($approvedcontexts);

        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));

        $approvedcontexts = new approved_contextlist($u3, 'block_motrain', [SYSCONTEXTID]);
        provider::delete_data_for_user($approvedcontexts);

        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertFalse($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));

        $approvedcontexts = new approved_contextlist($u1, 'block_motrain', [SYSCONTEXTID, $c2ctx->id]);
        provider::delete_data_for_user($approvedcontexts);

        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertFalse($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));
    }

    public function test_delete_data_for_users() {
        global $DB;

        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped("Privacy providers not installed");
        } else if (!interface_exists('core_privacy\local\request\core_userlist_provider')) {
            $this->markTestSkipped("Interface core_userlist_provider not available");
        }

        $this->resetAfterTest(true);
        extract($this->generate_test_data());

        $sysctx = context_system::instance();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));

        $userlist = new approved_userlist($c2ctx, 'block_motrain', [$u1->id]);
        provider::delete_data_for_users($userlist);

        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));

        $userlist = new approved_userlist($c1ctx, 'block_motrain', [$u1->id]);
        provider::delete_data_for_users($userlist);

        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));

        $userlist = new approved_userlist($sysctx, 'block_motrain', [$u1->id, $u2->id]);
        provider::delete_data_for_users($userlist);

        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => SYSCONTEXTID]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c1ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u1->id, 'contextid' => $c2ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => SYSCONTEXTID]));
        $this->assertTrue($DB->record_exists('block_motrain_log', ['userid' => $u2->id, 'contextid' => $c1ctx->id]));
        $this->assertFalse($DB->record_exists('block_motrain_playermap', ['userid' => $u1->id]));
        $this->assertTrue($DB->record_exists('block_motrain_playermap', ['userid' => $u3->id]));
    }

    public function test_extract_user_data() {
        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped("Privacy providers not installed");
        }
        $this->resetAfterTest(true);
        extract($this->generate_test_data());

        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $approvedcontexts = new approved_contextlist($u1, 'block_motrain', [SYSCONTEXTID, $c1ctx->id, $c2ctx->id]);
        provider::export_user_data($approvedcontexts);

        $writer = writer::with_context(context_system::instance());
        $logs = $writer->get_data([get_string('pluginname', 'block_motrain'),
            get_string('privacy:path:logs', 'block_motrain')]);
        $this->assertCount(1, $logs->data);
        $this->assertEquals(100, $logs->data[0]->coins);

        $mappings = $writer->get_data([get_string('pluginname', 'block_motrain'),
            get_string('privacy:path:mappings', 'block_motrain')]);
        $this->assertCount(2, $mappings->data);
        $this->assertEquals('account1', $mappings->data[0]->accountid);
        $this->assertEquals('account2', $mappings->data[1]->accountid);

        $writer = writer::with_context($c1ctx);
        $logs = $writer->get_data([get_string('pluginname', 'block_motrain'),
            get_string('privacy:path:logs', 'block_motrain')]);
        $this->assertCount(2, $logs->data);
        $this->assertEquals(99, $logs->data[0]->coins);
        $this->assertEquals(98, $logs->data[1]->coins);

        $mappings = $writer->get_data([get_string('pluginname', 'block_motrain'),
            get_string('privacy:path:mappings', 'block_motrain')]);
        $this->assertEmpty($mappings);
    }

    /**
     * Generate test data.
     *
     * @return array The associated items.
     */
    protected function generate_test_data() {
        global $DB;

        $dg = $this->getDataGenerator();
        $now = time();

        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $DB->insert_record('block_motrain_playermap', [
            'userid' => $u1->id,
            'accountid' => 'account1',
            'playerid' => 'playerid1',
        ]);
        $DB->insert_record('block_motrain_playermap', [
            'userid' => $u1->id,
            'accountid' => 'account2',
            'playerid' => 'playerid1',
            'blocked' => true,
            'blockedreason' => 'EMAIL_ALREADY_USED',
        ]);
        $DB->insert_record('block_motrain_playermap', [
            'userid' => $u3->id,
            'accountid' => 'account1',
            'playerid' => 'playerid3',
        ]);

        $DB->insert_record('block_motrain_log', [
            'userid' => $u1->id,
            'contextid' => context_system::instance()->id,
            'coins' => 100,
            'actionname' => 'some_action',
            'actionhash' => '',
            'timecreated' => 1,
            'timebroadcasted' => 1,
        ]);
        $DB->insert_record('block_motrain_log', [
            'userid' => $u1->id,
            'contextid' => context_course::instance($c1->id)->id,
            'coins' => 99,
            'actionname' => 'some_action',
            'actionhash' => '',
            'timecreated' => 1,
            'timebroadcasted' => 1,
        ]);
        $DB->insert_record('block_motrain_log', [
            'userid' => $u1->id,
            'contextid' => context_course::instance($c1->id)->id,
            'coins' => 98,
            'actionname' => 'some_action',
            'actionhash' => '',
            'timecreated' => 1,
            'timebroadcasted' => 1,
        ]);
        $DB->insert_record('block_motrain_log', [
            'userid' => $u1->id,
            'contextid' => context_course::instance($c2->id)->id,
            'coins' => 101,
            'actionname' => 'some_action',
            'actionhash' => '',
            'timecreated' => 1,
            'timebroadcasted' => 1,
        ]);
        $DB->insert_record('block_motrain_log', [
            'userid' => $u2->id,
            'contextid' => context_system::instance()->id,
            'coins' => 10,
            'actionname' => 'some_action',
            'actionhash' => '',
            'timecreated' => 1,
            'timebroadcasted' => 1,
        ]);
        $DB->insert_record('block_motrain_log', [
            'userid' => $u2->id,
            'contextid' => context_course::instance($c1->id)->id,
            'coins' => 9,
            'actionname' => 'some_action',
            'actionhash' => '',
            'timecreated' => 1,
            'timebroadcasted' => 1,
        ]);

        return [
            'c1' => $c1,
            'c2' => $c2,
            'now' => $now,
            'u1' => $u1,
            'u2' => $u2,
            'u3' => $u3,
            'u4' => $u4,
        ];
    }

    /**
     * Compare context lists.
     *
     * @param object $contextlist The list.
     * @param int[] $expectedids The IDs.
     */
    protected function assert_contextlist_equals($contextlist, $expectedids) {
        $contextids = array_map('intval', $contextlist->get_contextids());
        sort($contextids);
        sort($expectedids);
        $this->assertEquals($expectedids, $contextids);
    }

    /**
     * Compare user lists.
     *
     * @param object $userlist The list.
     * @param int[] $expectedids The IDs.
     */
    protected function assert_userlist_contains_userids($userlist, $expectedids) {
        $userids = array_map('intval', $userlist->get_userids());
        sort($userids);
        sort($expectedids);
        $this->assertEquals($expectedids, $userids);
    }

}

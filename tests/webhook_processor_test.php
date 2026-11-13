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

namespace block_motrain;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests.
 *
 * @package block_motrain
 * @copyright 2026 Mootivation Technologies Corp.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \block_motrain\local\webhook\processor
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\block_motrain\local\webhook\processor::class)]
final class webhook_processor_test extends tests\base_testcase {

    /**
     * Test.
     */
    public function test_manual_award_sends_notification() {
        set_config('accountid', 'account-one', 'block_motrain');
        set_config('sendlocalnotifications', 1, 'block_motrain');
        $dg = $this->getDataGenerator();
        $generator = $dg->get_plugin_generator('block_motrain');
        $u1 = $dg->create_user(['firstname' => 'Alice', 'lang' => 'en']);
        $generator->create_player_mapping([
            'userid' => $u1->id, 'playerid' => 'player-1',
        ]);
        $generator->create_message_template([
            'code' => \block_motrain\local\message_dealer::TYPE_USER_MANUAL_AWARD,
            'subject' => '[firstname], you earned [coins] coins',
            'content' => '<p>[message]</p>',
        ]);
        $sink = $this->redirectMessages();
        $processor = manager::instance()->get_webhook_processor();

        $processor->process_webhook('user.manuallyAwardedCoins', (object) [
            'account_id' => 'account-one', 'user_id' => 'player-1', 'coins' => 100, 'message' => 'Well done!',
        ]);

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertEquals($u1->id, $messages[0]->useridto);
        $this->assertEquals('Alice, you earned 100 coins', $messages[0]->subject);
        $this->assertEquals('<p>Well done!</p>', $messages[0]->fullmessagehtml);
        $sink->close();
    }

    /**
     * Test values.
     *
     * @return array
     */
    public static function ignored_notification_provider() {
        return [
            'notifications disabled' => [0, 'player-1', 'user.manuallyAwardedCoins'],
            'unknown player' => [1, 'unknown', 'user.manuallyAwardedCoins'],
            'unsupported event' => [1, 'player-1', 'user.unknownEvent'],
        ];
    }

    /**
     * Test.
     *
     * @param int $enabled Whether local notifications are enabled.
     * @param string $playerid Payload player.
     * @param string $type Webhook type.
     * @dataProvider ignored_notification_provider
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('ignored_notification_provider')]
    public function test_ignored_notification($enabled, $playerid, $type) {
        set_config('accountid', 'account-one', 'block_motrain');
        set_config('sendlocalnotifications', $enabled, 'block_motrain');
        $dg = $this->getDataGenerator();
        $generator = $dg->get_plugin_generator('block_motrain');
        $u1 = $dg->create_user();
        $generator->create_player_mapping([
            'userid' => $u1->id, 'playerid' => 'player-1',
        ]);
        $generator->create_message_template([
            'code' => \block_motrain\local\message_dealer::TYPE_USER_MANUAL_AWARD,
        ]);
        $sink = $this->redirectMessages();
        $processor = manager::instance()->get_webhook_processor();

        $processor->process_webhook($type, (object) [
            'account_id' => 'account-one', 'user_id' => $playerid, 'coins' => 100,
        ]);

        $this->assertCount(0, $sink->get_messages());
        $sink->close();
    }

    /**
     * Test.
     */
    public function test_wrong_account_is_rejected() {
        set_config('accountid', 'account-one', 'block_motrain');
        $processor = manager::instance()->get_webhook_processor();

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('accountidmismatch', 'block_motrain'));
        $processor->process_webhook('user.manuallyAwardedCoins', (object) [
            'account_id' => 'other-account', 'user_id' => 'player-1', 'coins' => 100,
        ]);
    }
}

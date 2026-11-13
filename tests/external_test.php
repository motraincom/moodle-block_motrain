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
 * @package    block_motrain
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \block_motrain\external::award_coins
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\block_motrain\external::class, 'award_coins')]
final class external_test extends tests\base_testcase {

    /**
     * Test.
     */
    public function test_award_coins() {
        global $CFG;

        if (empty($CFG->phpunit_block_motrain_apihost)) {
            $this->markTestSkipped('Set phpunit_block_motrain_apihost in config.php to run API tests.');
        }
        set_config('apihost', $CFG->phpunit_block_motrain_apihost, 'block_motrain');
        set_config('apikey', 'test-key', 'block_motrain');
        set_config('accountid', 'account-one', 'block_motrain');
        set_config('usecohorts', 0, 'block_motrain');
        tests\api_server_test_client::reset_instance();
        tests\api_server_test_client::instance()->before_scenario();
        $manager = manager::instance();
        $manager->check_enabled_state();

        $dg = $this->getDataGenerator();
        $u1 = $dg->create_user(['email' => 'p3@example.com']);
        $dg->get_plugin_generator('block_motrain')->create_team_mapping(['team_id' => 'team-one']);
        $this->setAdminUser();
        $this->assertEquals(0, $manager->get_client()->get_balance('player-3'));

        $result = external::award_coins($u1->id, 100);
        $result = external::clean_returnvalue(external::award_coins_returns(), $result);

        $this->assertEquals(['success' => true], $result);
        $this->assertEquals(100, $manager->get_client()->get_balance('player-3'));
    }

    /**
     * Test.
     */
    public function test_award_coins_requires_permission() {
        set_config('apihost', 'http://example.invalid', 'block_motrain');
        set_config('apikey', 'test-key', 'block_motrain');
        set_config('accountid', 'account-one', 'block_motrain');
        set_config('isenabled', 1, 'block_motrain');
        $dg = $this->getDataGenerator();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $this->setUser($u1);

        $this->expectException(\required_capability_exception::class);
        external::award_coins($u2->id, 100);
    }
}

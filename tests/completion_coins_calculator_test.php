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
 * @covers \block_motrain\local\completion_coins_calculator
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\block_motrain\local\completion_coins_calculator::class)]
final class completion_coins_calculator_test extends tests\base_testcase {

    /**
     * Test.
     */
    public function test_recommended_rules() {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $a1 = $dg->create_module('page', ['course' => $c1->id]);
        $calculator = new \block_motrain\local\completion_coins_calculator();

        $this->assertEquals(30, $calculator->get_course_coins($c1->id));
        $this->assertEquals(2, $calculator->get_module_coins($c1->id, $a1->cmid));
    }

    /**
     * Test values.
     *
     * @return array
     */
    public static function specific_rules_override_global_rules_provider() {
        return [
            'positive award' => [100, 25],
            'explicit zero' => [0, 0],
        ];
    }

    /**
     * Test.
     *
     * @param int $coursecoins Course award.
     * @param int $activitycoins Activity award.
     * @dataProvider specific_rules_override_global_rules_provider
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('specific_rules_override_global_rules_provider')]
    public function test_specific_rules_override_global_rules($coursecoins, $activitycoins) {
        $dg = $this->getDataGenerator();
        $generator = $dg->get_plugin_generator('block_motrain');
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $a1 = $dg->create_module('page', ['course' => $c1->id]);
        $a2 = $dg->create_module('page', ['course' => $c1->id]);
        $generator->create_completion_rule(['coins' => 50]);
        $generator->create_completion_rule(['modname' => 'page', 'coins' => 10]);
        $generator->create_completion_rule(['courseid' => $c1->id, 'coins' => $coursecoins]);
        $generator->create_completion_rule(['cmid' => $a1->cmid, 'coins' => $activitycoins]);
        $calculator = new \block_motrain\local\completion_coins_calculator();

        $this->assertEquals($coursecoins, $calculator->get_course_coins($c1->id));
        $this->assertEquals(50, $calculator->get_course_coins($c2->id));
        $this->assertEquals($activitycoins, $calculator->get_module_coins($c1->id, $a1->cmid));
        $this->assertEquals(10, $calculator->get_module_coins($c1->id, $a2->cmid));
    }
}

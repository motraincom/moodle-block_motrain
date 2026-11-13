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
 * @covers \block_motrain\local\message_dealer
 */
final class message_dealer_test extends tests\base_testcase {

    /**
     * Test values.
     *
     * @return array
     */
    public static function resolve_best_template_provider() {
        return [
            'exact language' => ['fr_ca', 1, 'regional'],
            'parent language' => ['fr_be', 1, 'french'],
            'fallback' => ['de', 1, 'fallback'],
            'disabled language' => ['fr_ca', 0, 'french'],
        ];
    }

    /**
     * Test.
     *
     * @param string $lang Recipient language.
     * @param int $enabled Whether the regional template is enabled.
     * @param string $expected Expected template.
     * @dataProvider resolve_best_template_provider
     */
    public function test_resolve_best_template($lang, $enabled, $expected) {
        $dg = $this->getDataGenerator();
        $generator = $dg->get_plugin_generator('block_motrain');
        $code = \block_motrain\local\message_dealer::TYPE_USER_MANUAL_AWARD;
        $templates = [
            'fallback' => $generator->create_message_template(['code' => $code]),
            'french' => $generator->create_message_template(['code' => $code, 'lang' => 'fr']),
            'regional' => $generator->create_message_template(['code' => $code, 'lang' => 'fr_ca', 'enabled' => $enabled]),
        ];
        $dealer = new \block_motrain\local\message_dealer();

        $this->assertEquals($templates[$expected]->id, $dealer->resolve_best_template($code, $lang)->id);
    }

    /**
     * Test.
     */
    public function test_no_matching_template() {
        $dg = $this->getDataGenerator();
        $code = \block_motrain\local\message_dealer::TYPE_USER_MANUAL_AWARD;
        $dg->get_plugin_generator('block_motrain')->create_message_template(['code' => $code, 'lang' => 'fr']);
        $dealer = new \block_motrain\local\message_dealer();

        $this->assertNull($dealer->resolve_best_template($code, 'en'));
    }

    /**
     * Test.
     */
    public function test_generate_content() {
        $dg = $this->getDataGenerator();
        $u1 = $dg->create_user(['firstname' => 'Alice', 'lastname' => 'Example']);
        $template = $dg->get_plugin_generator('block_motrain')->create_message_template([
            'code' => \block_motrain\local\message_dealer::TYPE_USER_MANUAL_AWARD,
            'subject' => '[firstname] [lastname] earned [coins] coins',
            'content' => '<p>[fullname]: [message]</p>',
        ]);
        $dealer = new \block_motrain\local\message_dealer();

        $this->assertEquals([
            'Alice Example earned 100 coins',
            '<p>Alice Example: Helping &lt;someone&gt; &amp; learning</p>',
        ], $dealer->generate_content($template, $u1, (object) [
            'coins' => 100,
            'message' => 'Helping <someone> & learning',
        ]));
    }
}

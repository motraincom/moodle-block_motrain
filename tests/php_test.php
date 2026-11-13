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
 * @coversNothing
 */
final class php_test extends tests\base_testcase {

    /**
     * PHP files provider.
     *
     * @return \Generator
     */
    public static function php_files_provider(): \Generator {
        global $CFG;

        $pluginroot = $CFG->dirroot . '/blocks/motrain';
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME;
        $browser = new \AppendIterator();
        $browser->append(new \FilesystemIterator($pluginroot, $flags));
        $browser->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pluginroot . '/classes', $flags)));
        $browser->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pluginroot . '/lang', $flags)));
        $browser = new \RegexIterator($browser, '/\.php$/');

        // These files execute requests or depend on variables supplied by their caller.
        $ignorefiles = [
            '/iframe.php',
            '/index.php',
            '/settings.php',
            '/settings_action.php',
            '/settings_addons.php',
            '/settings_config.php',
            '/settings_message_templates.php',
            '/settings_players.php',
            '/settings_rules.php',
            '/settings_teams.php',
            '/settings_teams_mismatch.php',
            '/version.php',
            '/webhook.php',
        ];

        foreach ($browser as $file) {
            $relpath = substr($file, strlen($pluginroot));
            if (in_array($relpath, $ignorefiles)) {
                continue;
            }
            yield $relpath => [$relpath];
        }
    }

    /**
     * Test.
     *
     * @param string $relpath The plugin-relative path.
     * @dataProvider php_files_provider
     */
    public function test_file_inclusion($relpath): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/adminlib.php');

        try {
            require_once($CFG->dirroot . '/blocks/motrain' . $relpath);
        } catch (\Throwable $e) {
            $this->fail("Failed to include file $relpath: " . $e->getMessage());
        }
        $this->addToAssertionCount(1);
    }
}

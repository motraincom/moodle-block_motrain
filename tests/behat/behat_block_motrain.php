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

// No MOODLE_INTERNAL check: Behat may load this file before config.php.
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Motrain Behat setup.
 *
 * @package    block_motrain
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class behat_block_motrain extends behat_base {

    /**
     * Before scenario.
     *
     * @BeforeScenario
     * @param BeforeScenarioScope $scope The scenario scope.
     */
    public function before_scenario(BeforeScenarioScope $scope): void {
        global $CFG;

        $tags = array_merge($scope->getFeature()->getTags(), $scope->getScenario()->getTags());
        if (!preg_grep('/^@?(block_motrain|local_motrainaddon\w*)$/', $tags)) {
            return;
        }

        if (empty($CFG->behat_block_motrain_apihost)) {
            throw new \coding_exception('Set behat_block_motrain_apihost in config.php.');
        }

        set_config('apikey', 'test-key', 'block_motrain');
        set_config('apihost', $CFG->behat_block_motrain_apihost, 'block_motrain');
        set_config('accountid', 'account-one', 'block_motrain');

        \block_motrain\tests\api_server_test_client::reset_instance();
        \block_motrain\tests\api_server_test_client::instance()->before_scenario();

        // Discard services cached by the previous scenario in this CLI process.
        \block_motrain\manager::reset_instance();
        \block_motrain\manager::instance()->check_enabled_state();
    }

}

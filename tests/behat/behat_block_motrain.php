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
use Behat\Gherkin\Node\TableNode;

/**
 * Motrain Behat setup.
 *
 * @package    block_motrain
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class behat_block_motrain extends behat_base {

    /** @var BeforeScenarioScope Pending scenario setup. */
    protected $scenarioscope;

    /**
     * Remember the scenario so setup can run before its first step.
     *
     * Moodle runs core's BeforeScenario hooks first, but Totara can run ours before
     * core resets the database. Configuring the plugin here would therefore have
     * that configuration erased by Totara's reset. Defer setup to BeforeStep,
     * after all BeforeScenario hooks have finished.
     *
     * @BeforeScenario
     * @param BeforeScenarioScope $scope The scenario scope.
     */
    public function before_scenario(BeforeScenarioScope $scope): void {
        $this->scenarioscope = $scope;
    }

    /**
     * Apply the pending setup once, immediately before the scenario's first step.
     *
     * Core's database reset has finished on both Moodle and Totara by this point.
     * Clearing the saved scope prevents subsequent steps from repeating setup or
     * resetting the API server's state.
     *
     * @BeforeStep
     */
    public function before_step(): void {
        if (!$this->scenarioscope) {
            return;
        }
        $scope = $this->scenarioscope;
        $this->scenarioscope = null;
        $this->setup_scenario($scope);
    }

    /**
     * Set up the scenario.
     *
     * @param BeforeScenarioScope $scope The scenario scope.
     */
    protected function setup_scenario(BeforeScenarioScope $scope): void {
        global $CFG;

        $tags = array_merge($scope->getFeature()->getTags(), $scope->getScenario()->getTags());
        if (!preg_grep('/^@?(block_motrain|(?:local|block)_motrainaddon\w*)$/', $tags)) {
            return;
        }

        if (empty($CFG->behat_block_motrain_apihost)) {
            throw new \coding_exception('Set behat_block_motrain_apihost in config.php.');
        }

        if (!file_exists($CFG->libdir . '/behat/classes/behat_generator_base.php')) {
            require_once(__DIR__ . '/../generator/behat_block_motrain_generator.php');
            (new behat_block_motrain_generator())->register();
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

    /**
     * Select the test API endpoint.
     *
     * @When /^I set the Incentli API endpoint to the test server$/
     */
    public function i_set_the_api_endpoint_to_the_test_server(): void {
        global $CFG;

        $this->execute('behat_forms::i_set_the_field_to', ['API endpoint', $CFG->behat_block_motrain_apihost]);
    }

    /**
     * Disable add-ons.
     *
     * @Given /^I disable the following Incentli addons:$/
     * @param TableNode $addons Short add-on names, such as welcomecoins.
     */
    public function i_disable_the_following_incentli_addons(TableNode $addons): void {
        $pluginmanager = \core_plugin_manager::instance();
        foreach ($addons->getColumn(0) as $name) {
            foreach (['local', 'block'] as $type) {
                $component = $type . '_motrainaddon' . $name;
                if ($pluginmanager->get_plugin_info($component) && get_config($component, 'enabled')) {
                    set_config('enabled', 0, $component);
                }
            }
        }
    }

    /**
     * Remove coin rules.
     *
     * @Given /^I remove all Incentli coin rules$/
     */
    public function i_remove_all_incentli_coin_rules(): void {
        global $DB;

        $DB->delete_records('block_motrain_comprules');
        (new \block_motrain\local\completion_coins_calculator())->purge_cache();
    }

    /**
     * Click in a message template section.
     *
     * @When /^I click on "([^"]*)" "([^"]*)" in the "([^"]*)" message template section$/
     * @param string $element The element to click.
     * @param string $selectortype The selector type.
     * @param string $section The section heading.
     */
    public function i_click_in_message_template_section($element, $selectortype, $section): void {
        $heading = behat_context_helper::escape($section);
        $xpath = "//table[preceding::h3[1][normalize-space(.)=$heading]]";
        $this->execute('behat_general::i_click_on_in_the', [$element, $selectortype, $xpath, 'xpath_element']);
    }

}

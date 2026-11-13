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

defined('MOODLE_INTERNAL') || die();

// Totara uses the core Behat context to dispatch plugin generators.
if (class_exists('behat_generator_base')) {
    class_alias('behat_generator_base', 'block_motrain_behat_generator_base');
} else {
    class_alias('behat_data_generators', 'block_motrain_behat_generator_base');
}

/**
 * Motrain Behat generator.
 *
 * @package    block_motrain
 * @category   test
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class behat_block_motrain_generator extends block_motrain_behat_generator_base {

    /**
     * Register the entities with Totara's core Behat generator.
     */
    public function register(): void {
        foreach ($this->get_creatable_entities() as $name => $definition) {
            self::$elements['block_motrain > ' . $name] = ['plugin' => 'block_motrain'] + $definition;
        }
    }

    /**
     * Get creatable entities.
     *
     * @return array The entities.
     */
    protected function get_creatable_entities(): array {
        return [
            'account settings' => [
                'datagenerator' => 'account_setting',
                'required' => ['name', 'value'],
            ],
            'completion rules' => [
                'datagenerator' => 'completion_rule',
                'required' => ['coins'],
                'switchids' => ['course' => 'courseid'],
            ],
            'message templates' => [
                'datagenerator' => 'message_template',
                'required' => ['code'],
            ],
            'team mappings' => [
                'datagenerator' => 'team_mapping',
                'required' => ['team_id'],
                'switchids' => ['cohort' => 'cohortid'],
            ],
        ];
    }

}

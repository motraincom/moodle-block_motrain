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

/**
 * Motrain generator.
 *
 * @package    block_motrain
 * @category   test
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class block_motrain_generator extends testing_block_generator {

    /**
     * Set an account setting.
     *
     * @param object|array $record The setting name and value. Use features.name or branding.name for nested settings.
     */
    public function create_account_setting($record): void {
        $record = (object) $record;
        $value = json_decode($record->value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $value = $record->value;
        }
        $keys = explode('.', $record->name, 2);
        $data = count($keys) === 2 ? [$keys[0] => [$keys[1] => $value]] : [$keys[0] => $value];
        \block_motrain\tests\api_server_test_client::instance()->configure_account($data);
    }

    /**
     * Create a team mapping.
     *
     * @param object|array $record The mapping data. Omit cohortid for the global association.
     * @return object The mapping.
     */
    public function create_team_mapping($record): object {
        global $DB;

        $record = (object) $record;
        $mapping = (object) [
            'accountid' => get_config('block_motrain', 'accountid'),
            'cohortid' => $record->cohortid ?? -1,
            'teamid' => $record->team_id,
        ];
        $mapping->id = $DB->insert_record('block_motrain_teammap', $mapping);
        return $mapping;
    }

}

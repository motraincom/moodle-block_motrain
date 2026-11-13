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

namespace block_motrain\tests;

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
trait generator {

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
     * Create a completion rule.
     *
     * @param object|array $record The rule data. Use cmid or an activity idnumber. Omit courseid for a global rule.
     * @return object The rule.
     */
    public function create_completion_rule($record): object {
        global $DB;

        $record = (object) $record;
        if (isset($record->activity)) {
            $record->cmid = $DB->get_field('course_modules', 'id', ['idnumber' => $record->activity], MUST_EXIST);
        }
        $rule = (object) [
            'courseid' => $record->courseid ?? 0,
            'cmid' => $record->cmid ?? 0,
            'modname' => $record->modname ?? null,
            'coins' => $record->coins,
        ];
        if ($rule->cmid) {
            $rule->courseid = $DB->get_field('course_modules', 'course', ['id' => $rule->cmid], MUST_EXIST);
        }
        $rule->id = $DB->insert_record('block_motrain_comprules', $rule);
        (new \block_motrain\local\completion_coins_calculator())->purge_cache();
        return $rule;
    }

    /**
     * Create a message template.
     *
     * @param object|array $record The template data.
     * @return object The template.
     */
    public function create_message_template($record): object {
        global $DB;

        $record = (object) $record;
        $template = (object) [
            'code' => $record->code,
            'lang' => $record->lang ?? null,
            'subject' => $record->subject ?? 'Congratulations [firstname]!',
            'content' => $record->content ?? '<p>[message]</p>',
            'contentformat' => $record->contentformat ?? FORMAT_HTML,
            'enabled' => $record->enabled ?? 1,
        ];
        $template->id = $DB->insert_record('block_motrain_msgtpl', $template);
        return $template;
    }

    /**
     * Create a local player mapping.
     *
     * @param object|array $record The mapping data.
     * @return object The mapping.
     */
    public function create_player_mapping($record): object {
        global $DB;

        $record = (object) $record;
        $mapping = (object) [
            'userid' => $record->userid,
            'accountid' => $record->accountid ?? get_config('block_motrain', 'accountid'),
            'playerid' => $record->playerid,
        ];
        $mapping->id = $DB->insert_record('block_motrain_playermap', $mapping);
        return $mapping;
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

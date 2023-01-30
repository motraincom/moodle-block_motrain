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

/**
 * Program coins calculator.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use cache;

defined('MOODLE_INTERNAL') || die();

/**
 * Program coins calculator.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class program_coins_calculator {

    /** @var cache */
    protected $cache;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->cache = cache::make('block_motrain', 'programrules');
    }

    /**
     * Get the coins for a program.
     *
     * @param int $programid The program ID.
     * @return int
     */
    public function get_program_coins($programid) {
        $rule = $this->get_program_rule($programid);
        if (isset($rule->coins)) {
            return $rule->coins;
        }
        return $this->get_program_default();
    }

    /**
     * Load the program default.
     *
     * @return int
     */
    protected function get_program_default() {
        $globalrule = $this->get_global_rule();
        return !empty($globalrule->program) ? $globalrule->program : 0;
    }

    /**
     * Get the program rule.
     *
     * @return object
     */
    protected function get_program_rule($programid) {
        $programid = max(1, (int) $programid); // Prevent 0, as reserved for global.
        if (($rule = $this->cache->get($programid)) === false) {
            $rule = $this->load_program_rule($programid);
            $this->cache->set($programid, $rule);
        }
        return $rule;
    }

    /**
     * Get the global rule.
     *
     * @return object
     */
    protected function get_global_rule() {
        $programid = 0;
        if (($rule = $this->cache->get($programid)) === false) {
            $rule = $this->load_global_rule();
            $this->cache->set($programid, $rule);
        }
        return $rule;
    }

    /**
     * Load the program rule.
     *
     * @param int $programid The program ID.
     * @return object
     */
    protected function load_program_rule($programid) {
        global $DB;

        $rule = (object) ['coins' => null];
        $records = $DB->get_records('block_motrain_programrules', ['programid' => $programid]);
        foreach ($records as $record) {
            $rule->coins = (int) $record->coins;
        }
        return $rule;
    }

    /**
     * Load the global rule.
     *
     * @return object
     */
    protected function load_global_rule() {
        global $DB;

        $userecommended = true;
        $rule = (object) ['program' => null];
        $records = $DB->get_records('block_motrain_programrules', ['programid' => 0]);
        foreach ($records as $record) {
            $userecommended = false;
            $rule->program = (int) $record->coins;
        }

        if ($userecommended) {
            $rule = static::get_recommended();
        }

        return $rule;
    }

    /**
     * Purge the cache.
     *
     * Ideally this should not be part of the public API.
     *
     * @return void
     */
    public function purge_cache() {
        $this->cache->purge();
    }

    /**
     * Get the recommended global values.
     *
     * @return object
     */
    public static function get_recommended() {
        // This has the same format as the global rule.
        return (object) [
            'program' => 100
        ];
    }

    /**
     * Read all the rules.
     *
     * @return object
     */
    public static function get_all_rules() {
        global $DB;

        $globalrules = (object) ['program' => null];
        $rules = [];

        $recordset = $DB->get_recordset('block_motrain_programrules', []);
        foreach ($recordset as $record) {
            if (empty($record->programid)) {
                $globalrules->program = (int) $record->coins;
                continue;
            }

            if (empty($rules[$record->programid])) {
                $rules[$record->programid] = (object) ['id' => (int) $record->programid, 'coins' => null];
            }
            $rules[$record->programid]->coins = (int) $record->coins;
        }
        $recordset->close();

        return (object) [
            'global' => (object) [
                'program' => $globalrules->program,
            ],
            'rules' => array_values($rules),
        ];
    }

}

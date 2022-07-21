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
 * Upgrade.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/db/upgradelib.php");

/**
 * Upgrade.
 *
 * @param int $oldversion The old version.
 * @return true
 */
function xmldb_block_motrain_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022072100) {

        // Define field metadatahash to be added to block_motrain_playermap.
        $table = new xmldb_table('block_motrain_playermap');
        $field = new xmldb_field('metadatahash', XMLDB_TYPE_CHAR, '40', null, null, null, null, 'playerid');

        // Conditionally launch add field metadatahash.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2022072100, 'motrain');
    }

    if ($oldversion < 2022072101) {

        // Define field metadatastale to be added to block_motrain_playermap.
        $table = new xmldb_table('block_motrain_playermap');
        $field = new xmldb_field('metadatastale', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'metadatahash');

        // Conditionally launch add field metadatastale.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2022072101, 'motrain');
    }

    return true;
}

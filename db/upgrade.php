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

    if ($oldversion < 2022072102) {

        // Define index useraccountmetadata (not unique) to be added to block_motrain_playermap.
        $table = new xmldb_table('block_motrain_playermap');
        $index = new xmldb_index('useraccountmetadata', XMLDB_INDEX_NOTUNIQUE, ['userid', 'accountid', 'metadatahash']);

        // Conditionally launch add index useraccountmetadata.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2022072102, 'motrain');
    }

    if ($oldversion < 2023012100) {

        // Define table block_motrain_msgtpl to be created.
        $table = new xmldb_table('block_motrain_msgtpl');

        // Adding fields to table block_motrain_msgtpl.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '32', null, null, null, null);
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('content', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('contentformat', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_motrain_msgtpl.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_motrain_msgtpl.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2023012100, 'motrain');
    }

    if ($oldversion < 2023012101) {

        // Define index codelang (unique) to be added to block_motrain_msgtpl.
        $table = new xmldb_table('block_motrain_msgtpl');
        $index = new xmldb_index('codelang', XMLDB_INDEX_UNIQUE, ['code', 'lang']);

        // Conditionally launch add index codelang.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2023012101, 'motrain');
    }

    if ($oldversion < 2023012301) {

        // Define table block_motrain_programrules to be created.
        $table = new xmldb_table('block_motrain_programrules');

        // Adding fields to table block_motrain_programrules.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('coins', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_motrain_programrules.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table block_motrain_programrules.
        $table->add_index('programid', XMLDB_INDEX_UNIQUE, array('programid'));

        // Conditionally launch create table for block_motrain_programrules.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2023012301, 'motrain');
    }

    if ($oldversion < 2023052801) {

        // Ensure that the webhook is updated to match the latest requirements.
        $task = new \block_motrain\task\adhoc_update_webhook();
        \core\task\manager::queue_adhoc_task($task, true);

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2023052801, 'motrain');
    }

    if ($oldversion < 2023082202) {

        // Ensure that the webhook is updated to match the latest requirements.
        $task = new \block_motrain\task\adhoc_update_webhook();
        \core\task\manager::queue_adhoc_task($task, true);

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2023082202, 'motrain');
    }

    if ($oldversion < 2023082702) {

        // Define field teamid to be added to block_motrain_playermap.
        $table = new xmldb_table('block_motrain_playermap');
        $field = new xmldb_field('teamid', XMLDB_TYPE_CHAR, '128', null, null, null, null, 'playerid');

        // Conditionally launch add field teamid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2023082702, 'motrain');
    }

    if ($oldversion < 2023103002) {

        // Ensure that the webhook is updated to match the latest requirements.
        $task = new \block_motrain\task\adhoc_update_webhook();
        \core\task\manager::queue_adhoc_task($task, true);

        // Motrain savepoint reached.
        upgrade_block_savepoint(true, 2023103002, 'motrain');
    }

    return true;
}

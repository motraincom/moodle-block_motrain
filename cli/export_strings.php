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
 * Export strings..
 *
 * @package    block_motrain
 * @copyright  2018 Frédéric Massart
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/csvlib.class.php');


$usage = "Export the language strings to CSV.

Usage:
    # php export_string.php

Options:
    -p, --plugin=<component>     The plugin to export from (defaults to block_motrain).
    -h, --help                   Print this help.

Examples:

    # php export_strings.php > /tmp/all_strings.csv
        Export the list of language strings.
";

list($options, $unrecognised) = cli_get_params([
    'plugin' => 'block_motrain',
    'help' => false,
], [
    'p' => 'plugin',
    'h' => 'help'
]);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL.'  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($usage);
    exit(2);
}

$component = $options['plugin'];
list($plugintype, $pluginname) = core_component::normalize_component($component);
$plugindir = core_component::get_plugin_directory($plugintype, $pluginname);
if (!$plugindir || !is_dir($plugindir)) {
    cli_error('Invalid plugin, or directory not found.');
    exit(4);
}

$enstrings = block_motrain_cli_load_strings($component, 'en');
$otherlangs = array_diff(block_motrain_cli_get_languages($component), ['en']);
$otherstrings = array_reduce($otherlangs, function($carry, $lang) use ($component) {
    $carry[$lang] = block_motrain_cli_load_strings($component, $lang);
    return $carry;
}, []);

$columns = array_merge(['Identifier', 'en'], $otherlangs);
$identifiers = array_keys($enstrings);
sort($identifiers);

$writer = new csv_export_writer();
$writer->add_data($columns);
foreach ($identifiers as $identifier) {
    $data = array_merge([
        $identifier,
        $enstrings[$identifier]
    ], array_values(array_map(function($lang) use($identifier, $otherstrings) {
        return !empty($otherstrings[$lang][$identifier]) ? $otherstrings[$lang][$identifier] : '';
    }, $otherlangs)));
    $writer->add_data($data);
}

$csvdata = $writer->print_csv_data(true);
mtrace($csvdata);

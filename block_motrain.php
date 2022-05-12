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
 * Block.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/moodleblock.class.php');

/**
 * Block.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_motrain extends block_base {

    /**
     * Applicable formats.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * The plugin has a settings.php file.
     *
     * @return boolean True.
     */
    public function has_config() {
        return true;
    }

    /**
     * Init.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('defaulttitle', 'block_motrain');
    }

    /**
     * Get content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $PAGE, $USER;

        if (isset($this->content)) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $config = $this->config;
        $manager = manager::instance();
        $canmanage = has_capability('block/motrain:addinstance', $PAGE->context);
        $canview = $canmanage || has_capability('block/motrain:view', $PAGE->context);

        // Hide the block to non-logged in users, guests and those who cannot view the block.
        if (!$USER->id || isguestuser() || !$canview) {
            return $this->content;
        }

        // Get the user's team.
        $teamid = $manager->get_team_resolver()->get_team_id_for_user($USER->id);
        if (!$teamid && !$canmanage) {
            return $this->content;
        }

        $renderer = $this->page->get_renderer('block_motrain');

        if (!$manager->is_enabled()) {
            $this->content->text = $renderer->notification(get_string('notenabled', 'block_motrain'), 'info');
            return $this->content;
        }

        $this->content->text = $renderer->main_block_content($manager, null, $config);

        // Extend block content hook.
        // $plugins = get_plugin_list_with_function('mootivatedaddon', 'extend_block_mootivated_content');
        // foreach ($plugins as $pluginname => $functionname) {
        //     $this->content->text .= component_callback($pluginname, 'extend_block_mootivated_content',
        //         [$manager, $school, $renderer], '');
        // }

        // Include the footer.
        if (!empty($config->footercontent) && !empty($config->footercontent['text'])) {
            $this->content->footer = format_text($config->footercontent['text'], $config->footercontent['format'], [
                'context' => $this->context
            ]);
        }

        return $this->content;
    }

    /**
     * Specialization.
     *
     * Happens right after the initialisation is complete.
     *
     * @return void
     */
    public function specialization() {
        parent::specialization();
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

}
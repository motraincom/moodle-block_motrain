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
 * Renderer.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_motrain_renderer extends plugin_renderer_base {

    /** @var bool Whether we already retrieved the appearance page requirements. */
    protected $appearancepagerequirementset;

    /**
     * Coin amount.
     *
     * @return string
     */
    public function coin_amount($coins) {
        $thousandssep = get_string('thousandssep', 'langconfig');
        $decsep = get_string('decsep', 'langconfig');
        $amount = $coins;
        return number_format($amount, 0, $decsep, $thousandssep);
    }

    /**
     * Get appearance PAGE requirements.
     *
     * Call this when you know that an AMD module will be using the appearance settings,
     * and append the returned value to tyour
     *
     * @return string
     */
    public function get_appearance_page_requirements() {
        if ($this->appearancepagerequirementset) {
            return '';
        }
        $this->appearancepagerequirementset = true;

        $id = html_writer::random_id();
        $data = $this->get_appearance_settings();
        $this->page->requires->js_amd_inline('
            require(["jquery", "block_motrain/appearance"], function($, Appearance) {
                var data = JSON.parse($("#' . $id . '").text());
                Appearance.setup(data);
            });
        ');

        return $this->json_script($data, $id);
    }

    /**
     * Get all the appearance settings.
     *
     * @return object
     */
    public function get_appearance_settings() {
        $manager = manager::instance();
        return (object) [
            'thousandssep' => get_string('thousandssep', 'langconfig'),
            'pointsimageurl' => $manager->get_coins_image_url()->out(false),
        ];
    }

    /**
     * Output a JSON script.
     *
     * @param mixed $data The data.
     * @param string $id The HTML ID to use.
     * @return string
     */
    public function json_script($data, $id) {
        $jsondata = json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        return html_writer::tag('script', $jsondata, ['id' => $id, 'type' => 'application/json']);
    }

    /**
     * Level.
     *
     * @param manager $manager The manager.
     * @param stdClass $level The level.
     * @return string
     */
    public function level(stdClass $level) {
        return $this->render_from_template('block_motrain/level', (object) array_merge(
            (array) $level,
            [
                'coins_in_level_formatted' => $this->coin_amount($level->coins_in_level),
                'coins_needed_formatted' => $this->coin_amount($level->coins_needed),
                'levelnstr' => get_string('leveln', 'block_motrain', $level->level),
                'progress_width' => ($level->progress_ratio > 0 ? max(0.01, $level->progress_ratio) : 0) * 100,
            ]
        ));
    }

    /**
     * Return the block's content.
     *
     * @param manager $manager The manager.
     * @return string
     */
    public function main_block_content(manager $manager) {
        global $USER;
        $level = $manager->get_level_proxy()->get_level($USER);
        $o = $this->render_from_template('block_motrain/block', [
            'levelhtml' => $level ? $this->level($level) : null,
            'haslevel' => !empty($level),
            'wallethtml' => $this->wallet($manager),
            'navhtml' => $this->navigation_on_block($manager),
        ]);
        return $o;
    }

    /**
     * Return the block's content.
     *
     * @param manager $manager The manager.
     * @return string
     */
    public function main_block_content_when_disabled(manager $manager) {
        $o = '';
        if ($manager->can_manage()) {
            $o .= $this->notification(get_string('notenabled', 'block_motrain'), 'info', false);
            $o .= $this->navigation_on_block_for_managers($manager);
        }
        return $o;
    }

    /**
     * Render navigation.
     *
     * @param manager $manager The manager.
     * @return string
     */
    public function navigation_for_managers(manager $manager, $currentpage) {
        $tabs = [
            new tabobject(
                'settings',
                new moodle_url('/blocks/motrain/settings_config.php'),
                get_string('settings', 'core')
            ),
            new tabobject(
                'rules',
                new moodle_url('/blocks/motrain/settings_rules.php'),
                get_string('coinrules', 'block_motrain')
            ),
            new tabobject(
                'teams',
                new moodle_url('/blocks/motrain/settings_teams.php'),
                get_string('teamassociations', 'block_motrain')
            ),
            new tabobject(
                'players',
                new moodle_url('/blocks/motrain/settings_players.php'),
                get_string('playersmapping', 'block_motrain')
            ),
            new tabobject(
                'messagetemplates',
                new moodle_url('/blocks/motrain/settings_message_templates.php'),
                get_string('messagetemplates', 'block_motrain')
            ),
            new tabobject(
                'addons',
                new moodle_url('/blocks/motrain/settings_addons.php'),
                get_string('manageaddons', 'block_motrain')
            ),
        ];
        return $this->tabtree($tabs, $currentpage);
    }

    /**
     * Render navigation.
     *
     * @param manager $manager The manager.
     * @return string
     */
    public function navigation_on_block(manager $manager) {
        global $USER;

        $embedurl = new moodle_url('/blocks/motrain/index.php');
        $urls = (object) [
            'info' => new moodle_url($embedurl, ['page' => 'info']),
            'store' => new moodle_url($embedurl, ['page' => 'shop']),
            'purchases' => new moodle_url($embedurl, ['page' => 'purchases']),
            'leaderboard' => new moodle_url($embedurl, ['page' => 'leaderboards']),
        ];

        $actions = [];
        $actions[] = new action_link(
            $urls->info,
            get_string('infopagetitle', 'block_motrain'),
            null,
            null,
            new pix_icon('info', '', 'block_motrain')
        );

        $actions[] = new action_link(
            $urls->store,
            get_string('store', 'block_motrain'),
            null,
            null,
            new pix_icon('store', '', 'block_motrain')
        );

        $actions[] = new action_link(
            $urls->purchases,
            get_string('purchases', 'block_motrain'),
            null,
            null,
            new pix_icon('purchases', '', 'block_motrain')
        );

        if ($manager->has_leaderboard_access($USER->id)) {
            $actions[] = new action_link(
                $urls->leaderboard,
                get_string('leaderboard', 'block_motrain'),
                null,
                null,
                new pix_icon('leaderboard', '', 'block_motrain')
            );
        }

        if (has_capability('block/motrain:accessdashboard', context_system::instance())) {
            $actions[] = new action_link(
                $manager->get_dashboard_url(),
                get_string('dashboard', 'block_motrain'),
                null,
                ['target' => '_blank'],
                new pix_icon('dashboard', '', 'block_motrain')
            );
        }

        if ($manager->can_manage()) {
            $actions[] = new action_link(
                new moodle_url('/blocks/motrain/settings_config.php'),
                get_string('settings', 'core'),
                null,
                null,
                new pix_icon('settings', '', 'block_motrain')
            );
        }

        return $this->render_navigation_on_block($actions);
    }

    /**
     * Render navigation.
     *
     * @param manager $manager The manager.
     * @return string
     */
    public function navigation_on_block_for_managers(manager $manager) {
        $actions = [];
        if (has_capability('block/motrain:accessdashboard', context_system::instance())) {
            $actions[] = new action_link(
                $manager->get_dashboard_url(),
                get_string('dashboard', 'block_motrain'),
                null,
                ['target' => '_blank'],
                new pix_icon('dashboard', '', 'block_motrain')
            );
        }
        if ($manager->can_manage()) {
            $actions[] = new action_link(
                new moodle_url('/blocks/motrain/settings_config.php'),
                get_string('settings', 'core'),
                null,
                null,
                new pix_icon('i/settings', '', 'core')
            );
        }
        return $this->render_navigation_on_block($actions);
    }


    /**
     * Override pix_url to auto-handle deprecation.
     *
     * @param string $image The file.
     * @param string $component The component.
     * @return string
     */
    public function pix_url($image, $component = 'moodle') {
        return $this->image_url($image, $component);
    }

    /**
     * Initialise a react module.
     *
     * @param string $module The AMD name of the module.
     * @param object|array $props The props.
     * @return void
     */
    public function react_module($module, $props) {
        $id = html_writer::random_id('block_motrain-react-app');
        $propsid = html_writer::random_id('block_motrain-react-app-props');
        $iconname = 'y/loading';

        $o = '';
        $o .= html_writer::start_div('block_motrain-react', ['id' => $id]);
        $o .= html_writer::start_div('block_motrain-react-loading');
        $o .= html_writer::start_div();
        $o .= $this->render(new pix_icon($iconname, 'loading'));
        $o .= ' ' . get_string('loadinghelp', 'core');
        $o .= html_writer::end_div();
        $o .= html_writer::end_div();
        $o .= html_writer::end_div();

        $o .= $this->json_script($props, $propsid);

        $this->page->requires->js_amd_inline("
            require(['block_motrain/launcher'], function(launcher) {
                launcher('$module', '$id', '$propsid');
            });
        ");

        return $o;
    }

    /**
     * Render navigation on block.
     *
     * @param array $actions A list of actions.
     */
    protected function render_navigation_on_block($actions) {
        if (empty($actions)) {
            return '';
        }

        $o = '';
        $o .= html_writer::start_tag('nav');
        $o .= implode('', array_map(function(action_link $action) {
            if (!isset($action->attributes['id'])) {
                $action->attributes['id'] = html_writer::random_id();
            }

            $iconandtext = html_writer::div($this->render($action->icon));
            $iconandtext .= html_writer::div($action->text, 'nav-label');
            $content = html_writer::link($action->url, $iconandtext, array_merge($action->attributes, ['class' => 'nav-button']));

            $componentactions = !empty($action->actions) ? $action->actions : [];
            foreach ($componentactions as $componentaction) {
                $this->add_action_handler($componentaction, $action->attributes['id']);
            }

            return $content;
        }, $actions));
        $o .= html_writer::end_tag('nav');

        return $o;
    }

    /**
     * Return the content of the user's wallet.
     *
     * @param manager $manager The manager.
     * @return string
     */
    public function wallet(manager $manager) {
        global $USER;
        $coins = $manager->get_balance_proxy()->get_balance($USER);
        return $this->render_from_template('block_motrain/wallet', (object) array_merge(
            (array) $this->get_appearance_settings(),
            [
                'coins' => $this->coin_amount($coins),
            ]
        ));
    }

}

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

use block_motrain\addons;
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
            'icondoubleurl' => $manager->get_metadata_reader()->get_icon_double_url(),
            'iconticketsurl' => $manager->get_metadata_reader()->get_tickets_icon_url(),
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
     * @deprecated Since 1.8.0.
     * @return string
     */
    public function level(stdClass $level) {
        debugging('This method level is deprecated without replacement, yet.', DEBUG_DEVELOPER);
        return '';
    }

    /**
     * Return the block's content.
     *
     * @param manager $manager The manager.
     * @param object $config The block config.
     * @return string
     */
    public function main_block_content(manager $manager, $config) {
        global $USER;

        $infourl = new moodle_url('/blocks/motrain/index.php', ['page' => 'info']);
        $storeurl = new moodle_url('/blocks/motrain/index.php', ['page' => 'shop']);
        $leaderboardsurl = new moodle_url('/blocks/motrain/index.php', ['page' => 'leaderboards']);

        $coins = $manager->get_balance_proxy()->get_balance($USER);
        $level = $manager->get_level_proxy()->get_level($USER);
        $hasticketsenabled = $manager->has_tickets_enabled($USER->id);
        $tickets = $hasticketsenabled ? $manager->get_balance_proxy()->get_tickets($USER) : 0;

        if ($level) {
            $level->coins_remaining_formatted = $this->coin_amount($level->coins_remaining);
            $level->levelnstr = get_string('leveln', 'block_motrain', $level->level);
        }

        $playernav = [];
        if ($manager->has_leaderboard_access($USER->id)) {
            $playernav[] = [
                'icon' => $this->render_from_template('block_motrain/nav-icon-leaderboard', []),
                'label' => get_string('leaderboard', 'block_motrain'),
                'url' => $leaderboardsurl->out(false),
            ];
        }

        $managernav = [];
        if (has_capability('block/motrain:accessdashboard', context_system::instance())) {
            $managernav[] = [
                'icon' => $this->render_from_template('block_motrain/nav-icon-dashboard', []),
                'label' => get_string('dashboard', 'block_motrain'),
                'url' => $manager->get_dashboard_url()->out(false),
            ];
        }
        if ($manager->can_manage()) {
            $url = new moodle_url('/blocks/motrain/settings_config.php');
            $managernav[] = [
                'icon' => $this->render_from_template('block_motrain/nav-icon-settings', []),
                'label' => get_string('settings', 'core'),
                'url' => $url->out(false),
            ];
        }

        // Extend player navigation from addons.
        $addonplayernav = [];
        $addons = addons::get_list_with_function('extend_block_motrain_player_navigation');
        foreach ($addons as $pluginname => $functionname) {
            $candidates = component_callback($pluginname, 'extend_block_motrain_player_navigation', [$manager, $this], []);
            if (empty($candidates)) {
                continue;
            }
            $addonplayernav = array_merge($addonplayernav, array_values(array_filter($candidates, function($item) {
                return !empty($item['url']) && !empty($item['label']);
            })));
        }
        $playernav = array_merge($playernav, $addonplayernav);

        return $this->render_from_template('block_motrain/block', [
            'coins' => $coins,
            'coins_formatted' => $this->coin_amount($coins),
            'tickets' => $tickets,
            'tickets_formatted' => $this->coin_amount($tickets),
            'showtickets' => $hasticketsenabled,
            'level' => $level,
            'infourl' => $infourl->out(false),
            'storeurl' => $storeurl->out(false),
            'hasplayernav' => !empty($playernav),
            'playernav' => $playernav,
            'hasmanagernav' => !empty($managernav),
            'managernav' => $managernav,
            'accentcolor' => $config->accentcolor ?? '',
            'bgcolor' => $config->bgcolor ?? '',
        ] + (array) $this->get_appearance_settings());
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
        return '';
    }

    /**
     * Render navigation.
     *
     * @param manager $manager The manager.
     * @return string
     */
    public function navigation_on_block_for_managers(manager $manager) {
        return '';
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
     * Return the content of the user's wallet.
     *
     * @param manager $manager The manager.
     * @deprecated Since 1.8.0.
     * @return string
     */
    public function wallet(manager $manager) {
        debugging('This method level is deprecated without replacement, yet.', DEBUG_DEVELOPER);
        return '';
    }

}

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
 * Block content.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\output;

use block_motrain\addons;
use block_motrain\manager;
use context_system;
use moodle_url;
use renderer_base;
use templatable;

/**
 * Block content.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_content implements templatable {

    /** @var manager The manager. */
    protected $manager;
    /** @var object The block config. */
    protected $config;
    /** @var int The user ID.  */
    protected $userid;

    /** @var bool Whether can access dashboard. */
    public $canaccessdashboard;
    /** @var bool Whether store elements are enabled. */
    public $hasstore;

    /** @var moodle_url The url. */
    public $dashboardurl;
    /** @var moodle_url The url. */
    public $infourl;
    /** @var moodle_url The url. */
    public $storeurl;
    /** @var moodle_url The url. */
    public $leaderboardsurl;
    /** @var moodle_url The url. */
    public $purchasesurl;
    /** @var moodle_url The url. */
    public $settingsurl;

    /**
     * Constructor.
     *
     * @param manager $manager The manager.
     * @param object $config The block config.
     */
    public function __construct(manager $manager, $config) {
        global $USER;

        $this->manager = $manager;
        $this->config = $config ?? (object) [];
        $this->userid = (int) $USER->id;

        $this->canaccessdashboard = has_capability('block/motrain:accessdashboard', context_system::instance());
        $this->hasstore = $manager->has_store();

        $this->infourl = new moodle_url('/blocks/motrain/index.php', ['page' => 'info']);
        $this->dashboardurl = $manager->get_dashboard_url();
        $this->storeurl = new moodle_url('/blocks/motrain/index.php', ['page' => 'shop']);
        $this->leaderboardsurl = new moodle_url('/blocks/motrain/index.php', ['page' => 'leaderboards']);
        $this->purchasesurl = new moodle_url('/blocks/motrain/index.php', ['page' => 'purchases']);
        $this->settingsurl = new moodle_url('/blocks/motrain/settings_config.php');;
    }

    /**
     * Get the block config.
     *
     * @return object
     */
    public function get_config() {
        return $this->config;
    }

    /**
     * Get the manager.
     *
     * @return manager;
     */
    public function get_manager() {
        return $this->manager;
    }

    /**
     * Get the user ID.
     *
     * @return int
     */
    public function get_userid() {
        return $this->config;
    }

    /**
     * Renderer.
     *
     * @param renderer_base $output Motrain renderer.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        if (!$output instanceof \block_motrain_renderer) {
            throw new \coding_exception('Invalid renderer');
        }

        $manager = $this->manager;
        $userid = $this->userid;

        $coins = $manager->get_balance_proxy()->get_balance($userid);
        $level = $this->get_level_data($output);
        $hasticketsenabled = $manager->has_tickets_enabled($userid);
        $tickets = $hasticketsenabled ? $manager->get_balance_proxy()->get_tickets($userid) : 0;
        $purchasespendingredeem = $this->hasstore ? $manager->get_purchase_proxy()->count_awaiting_redemption($userid) : 0;

        $playernav = $this->get_player_nav_items($output);
        $managernav = $this->get_manager_nav_items($output);

        // No keys can be removed from here because addons may rely on them.
        return [
            'coins' => $coins,
            'coins_formatted' => $output->coin_amount($coins),

            'tickets' => $tickets,
            'tickets_formatted' => $output->coin_amount($tickets),
            'showtickets' => $hasticketsenabled,

            'level' => $level,
            'showlevel' => !empty($level),

            'showstore' => $this->hasstore,
            'purchasespendingredeem' => $purchasespendingredeem,
            'haspurchasespendingredeem' => $purchasespendingredeem > 0,
            'haspurchasespendingredeemmorethan9' => $purchasespendingredeem > 9,

            'canaccessdashboard' => $this->canaccessdashboard,
            'canaccessleaderboards' => $manager->has_leaderboard_access($this->userid),
            'canmanage' => $manager->can_manage($this->userid),

            'hasplayernav' => !empty($playernav),
            'playernav' => $playernav,
            'hasmanagernav' => !empty($managernav),
            'managernav' => $managernav,

            'infourl' => $this->infourl->out(false),
            'storeurl' => $this->storeurl->out(false),
            'leaderboardsurl' => $this->leaderboardsurl->out(false),
            'purchasesurl' => $this->purchasesurl->out(false),
            'dashboardurl' => $this->dashboardurl->out(false),
            'settingsurl' => $this->settingsurl->out(false),

            'accentcolor' => $this->config->accentcolor ?? '',
            'bgcolor' => $this->config->bgcolor ?? '',
        ] + (array) $output->get_appearance_settings();
    }

    /**
     * Get the level data.
     *
     * @param renderer_base $output Motrain renderer.
     * @return object|null
     */
    protected function get_level_data(renderer_base $output) {
        $level = $this->manager->get_level_proxy()->get_level($this->userid);
        if ($level) {
            $level->coins_remaining_formatted = $output->coin_amount($level->coins_remaining);
            $level->levelnstr = get_string('leveln', 'block_motrain', $level->level);
        }
        return $level;
    }

    /**
     * Get the manager navigation items.
     *
     * @param renderer_base $output Motrain renderer.
     * @return array
     */
    protected function get_manager_nav_items(renderer_base $output) {
        $managernav = [];
        if ($this->canaccessdashboard) {
            $managernav[] = [
                'icon' => $output->render_from_template('block_motrain/nav-icon-dashboard', []),
                'label' => get_string('dashboard', 'block_motrain'),
                'url' => $this->dashboardurl->out(false),
            ];
        }
        if ($this->manager->can_manage()) {
            $managernav[] = [
                'icon' => $output->render_from_template('block_motrain/nav-icon-settings', []),
                'label' => get_string('settings', 'core'),
                'url' => $this->settingsurl->out(false),
            ];
        }
        return $managernav;
    }

    /**
     * Get the player navigation items.
     *
     * @param renderer_base $output Motrain renderer.
     * @return array
     */
    protected function get_player_nav_items(renderer_base $output) {
        $playernav = [];
        if ($this->manager->has_leaderboard_access($this->userid)) {
            $playernav[] = [
                'icon' => $output->render_from_template('block_motrain/nav-icon-leaderboard', []),
                'label' => get_string('leaderboard', 'block_motrain'),
                'url' => $this->leaderboardsurl->out(false),
            ];
        }

        // Extend player navigation from addons.
        $addonplayernav = [];
        $addons = addons::get_list_with_function('extend_block_motrain_player_navigation');
        foreach ($addons as $pluginname => $functionname) {
            $candidates = component_callback($pluginname, 'extend_block_motrain_player_navigation', [$this->manager, $this], []);
            if (empty($candidates)) {
                continue;
            }
            $addonplayernav = array_merge($addonplayernav, array_values(array_filter($candidates, function($item) {
                return !empty($item['url']) && !empty($item['label']);
            })));
        }
        $playernav = array_merge($playernav, $addonplayernav);

        return $playernav;
    }

}

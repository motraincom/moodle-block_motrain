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
 * Manager.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain;

use block_motrain\local\api_error;
use block_motrain\local\balance_proxy;
use block_motrain\local\client_exception;
use block_motrain\local\collection_strategy;
use block_motrain\local\player_mapper;
use block_motrain\local\team_resolver;
use block_motrain\local\user_pusher;
use block_motrain\task\adhoc_queue_cohort_members_for_push;
use cache;
use context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Manager.
 *
 * Note that the config (from get_config) should not be cached!
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var balance_proxy|null The balance proxy. */
    protected $balanceproxy;
    /** @var client|null The client. */
    protected $client;
    /** @var collection_strategy|null The collection strategy. */
    protected $collectionstrategy;
    /** @var cache The coins cache. */
    protected $coinscache;
    /** @var string|null The dashboard URL. */
    protected $dashboardurl;
    /** @var player_mapper|null The player mapper. */
    protected $playermapper;
    /** @var team_resolver|null The team resolver. */
    protected $teamresolver;
    /** @var user_pusher|null The user pusher. */
    protected $userpusher;
    /** @var static The singleton. */
    protected static $instance;

    public function __construct() {
    }

    public function get_account_id() {
        return get_config('block_motrain', 'accountid');
    }

    /**
     * Get the balance proxy.
     *
     * @return balance_proxy
     */
    public function get_balance_proxy() {
        if (!$this->balanceproxy) {
            $this->balanceproxy = new balance_proxy($this);
        }
        return $this->balanceproxy;
    }

    /**
     * Get the client.
     *
     * @param bool $reload Whether to reload the client.
     * @return client The client.
     */
    public function get_client($reload = false) {
        if (!isset($this->client) || $reload) {
            $apikey = get_config('block_motrain', 'apikey');
            $apihost = get_config('block_motrain', 'apihost');
            $accountid = get_config('block_motrain', 'accountid');
            $this->client = new client($apihost, $apikey, $accountid);
        }
        return $this->client;
    }

    /**
     * Get the coins image URL.
     *
     * @return moodle_url
     */
    public static function get_coins_image_url() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('block_motrain');
        $imagename = get_config('block_motrain', 'coinsimage');
        $url = null;
        if ($imagename) {
            $url = moodle_url::make_pluginfile_url(SYSCONTEXTID, 'block_motrain', 'coinsimage', 0, '', $imagename);
        }
        if (!$url) {
            $url = $renderer->pix_url('coins', 'block_motrain');
        }
        return $url;
    }

    /**
     * Get the collection strategy.
     *
     * @return collection_strategy
     */
    public function get_collection_strategy() {
        if (!$this->collectionstrategy) {
            $this->collectionstrategy = new collection_strategy($this->get_team_resolver(), $this->get_player_mapper(),
                $this->get_client(), $this->get_balance_proxy());
        }
        return $this->collectionstrategy;
    }

    /**
     * Get the dashboard URL.
     *
     * @param string $uri The path on the dashboard.
     */
    public function get_dashboard_url($uri = '/') {
        if (!$this->dashboardurl) {
            $dashboardurl = get_config('block_motrain', 'dashboardurl');
            $apihost = get_config('block_motrain', 'apihost');
            if (empty($dashboardurl) && !empty($apihost) && strpos($apihost, '://api.') > 0) {
                $dashboardurl = str_replace('://api.', '://dashboard.', $apihost);
            }
            $this->dashboardurl = rtrim($dashboardurl ? $dashboardurl : 'https://dashboard.motrainapp.com', '/');
        }
        return new moodle_url($this->dashboardurl . $uri);
    }

    public function get_global_team_association() {
        global $DB;
        $accountid = get_config('block_motrain', 'accountid');
        return $DB->get_record('block_motrain_teammap', ['accountid' => $accountid, 'cohortid' => -1]);
    }

    /**
     * Get the player mapper.
     *
     * @return player_mapper
     */
    public function get_player_mapper() {
        if (!$this->playermapper) {
            $this->playermapper = new player_mapper($this->get_client(), $this->get_account_id());
        }
        return $this->playermapper;
    }

    /**
     * Get setup hash.
     *
     * This is used to check whether the settings have changed.
     *
     * @return string
     */
    public function get_setup_hash() {
        $apikey = (string) get_config('block_motrain', 'apikey');
        $apihost = (string) get_config('block_motrain', 'apihost');
        $accountid = (string) get_config('block_motrain', 'accountid');
        return sha1(implode('|', [$apikey, $apihost, $accountid]));
    }

    /**
     * Get the team resolver.
     *
     * @return team_resolver
     */
    public function get_team_resolver() {
        if (!$this->teamresolver) {
            $this->teamresolver = new team_resolver($this->is_using_cohorts(), $this->get_account_id());
        }
        return $this->teamresolver;
    }

    /**
     * Get the user pusher.
     *
     * @return user_pusher
     */
    public function get_user_pusher() {
        if (!$this->userpusher) {
            $this->userpusher = new user_pusher($this->get_client(), $this->get_account_id(), $this->get_team_resolver(),
                $this->get_player_mapper());
        }
        return $this->userpusher;
    }

    public function has_team_associations() {
        global $DB;
        $accountid = get_config('block_motrain', 'accountid');
        return $DB->record_exists('block_motrain_teammap', ['accountid' => $accountid]);
    }

    /**
     * Whether we should automatically push users.
     *
     * @return bool
     */
    public function is_automatic_push_enabled() {
        return (bool) get_config('block_motrain', 'autopush');
    }

    /**
     * Whether the plugin is enabled.
     *
     * The plugin is disabled until it is setup properly.
     *
     * @return bool
     */
    public function is_enabled() {
        $isenabled = (bool) get_config('block_motrain', 'isenabled');
        if (!$isenabled) {
            return false;
        }
        return $this->is_setup();
    }

    /**
     * Whether the plugin seems setup.
     *
     * As in, its settings have been provided.
     *
     * @return bool
     */
    public function is_setup() {
        $apikey = get_config('block_motrain', 'apikey');
        $apihost = get_config('block_motrain', 'apihost');
        $accountid = get_config('block_motrain', 'accountid');
        return !empty($apikey) && !empty($apihost) && !empty($accountid);
    }

    /**
     * Whether we are using cohorts.
     *
     * @return bool
     */
    public function is_using_cohorts() {
        return (bool) get_config('block_motrain', 'usecohorts');
    }

    /**
     * Require enabled.
     *
     * @throws \moodle_exception
     */
    public function require_enabled() {
        if (!$this->is_enabled()) {
            throw new \moodle_exception('notenabled', 'block_motrain');
        }
    }

    /**
     * Require view permissions.
     *
     * @throws \required_capability_exception
     */
    public function require_view() {
        global $PAGE;
        require_capability('block/motrain:view', $PAGE->context);
    }

    /**
     * Schedule the synchronisation of a cohort.
     *
     * @param int $cohortid The cohort ID.
     * @param bool $totarasync Whether we should sync the Totara audience.
     */
    public function schedule_cohort_sync($cohortid, $totarasync = true) {
        $task = new adhoc_queue_cohort_members_for_push();
        $task->set_custom_data([
            'cohortid' => $cohortid,
            'totarasync' => $totarasync
        ]);
        $task->set_component('block_motrain');
        \core\task\manager::queue_adhoc_task($task);
    }

    /**
     * Get the manager's instance.
     *
     * @return static
     */
    public static function instance() {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

}
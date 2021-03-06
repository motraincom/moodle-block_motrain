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
use block_motrain\local\metadata_reader;
use block_motrain\local\player_mapper;
use block_motrain\local\team_resolver;
use block_motrain\local\user_pusher;
use block_motrain\task\adhoc_queue_cohort_members_for_push;
use cache;
use context;
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
    /** @var metadata_reader|null The metadata reader. */
    protected $metadatareader;
    /** @var player_mapper|null The player mapper. */
    protected $playermapper;
    /** @var team_resolver|null The team resolver. */
    protected $teamresolver;
    /** @var user_pusher|null The user pusher. */
    protected $userpusher;

    /** @var static The singleton. */
    protected static $instance;

    /**
     * Can the user earn.
     *
     * Note that this is unlikely to return true when the context is not a course context
     * because students are given the capability to earn coins. You should simply check
     * whether the user is a player for awards that do not depend on the capability to earn.
     *
     * @param int $userid The user ID.
     * @param context $context The context.
     * @return bool
     */
    public function can_earn_in_context($userid, context $context) {

        // First of all, the user must be a player.
        if (!$this->is_player($userid)) {
            return false;
        }

        // Check has capability in context.
        if (!has_capability('block/motrain:earncoins', $context, $userid)) {
            return false;
        }

        return true;
    }

    /**
     * Whether the user can manage.
     *
     * Managing means seeing the block, and navigating to its page, it does not
     * mean configuring the plugin. To configure the plugin, a user must be an admin.
     *
     * This permission is mostly useless, it really just serves to know who can see
     * the block even when they do not have the permission to view it, or are not player.
     */
    public function can_manage($userid = null) {
        global $PAGE;
        return has_capability('block/motrain:addinstance', $PAGE->context, $userid);
    }

    /**
     * Whether the user can view.
     *
     * This does not tell us whether the user is a player by the way. It is only
     * used to determine whether the user can see the block at all.
     */
    public function can_view($userid = null) {
        global $PAGE;
        return has_capability('block/motrain:view', $PAGE->context, $userid);
    }

    /**
     * Check the enabled state.
     */
    public function check_enabled_state() {
        if (!$this->is_setup()) {
            set_config('isenabled', false, 'block_motrain');
            return;
        }

        $client = $this->make_client();
        try {
            $account = $client->get_account();
        } catch (client_exception $e) {
            set_config('isenabled', false, 'block_motrain');
            return;
        }

        set_config('isenabled', true, 'block_motrain');
    }

    /**
     * Get the account ID.
     *
     * @return string|false
     */
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
     * @return client The client.
     */
    public function get_client() {
        if (!isset($this->client)) {
            $this->client = $this->make_client();
            $this->client->set_observer($this);
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
            $this->collectionstrategy = new collection_strategy($this);
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
     * Get the metadata reader.
     */
    public function get_metadata_reader() {
        if (!$this->metadatareader) {
            $this->metadatareader = new metadata_reader($this);
        }
        return $this->metadatareader;
    }

    /**
     * Get the player mapper.
     *
     * @return player_mapper
     */
    public function get_player_mapper() {
        if (!$this->playermapper) {
            $this->playermapper = new player_mapper($this->get_client(), $this->get_account_id());
            $this->playermapper->set_local_only($this->is_paused());
            $this->playermapper->set_sync_metadata($this->is_player_metadata_sync_enabled());
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

    /**
     * Whether user has access to a leaderboard.
     *
     * @return bool
     */
    public function has_leaderboard_access($userid) {
        $teamid = $this->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid && !$this->can_manage($userid)) {
            return false;
        }

        $metadata = $this->get_metadata_reader();
        if ($metadata->is_account_leaderboard_enabled()) {
            return true;
        } else if ($teamid && $metadata->is_team_leaderboard_enabled($teamid)) {
            return true;
        }

        if (!$this->can_manage($userid)) {
            return false;
        }

        return $metadata->is_any_known_team_leaderboard_enabled();
    }

    public function has_team_associations() {
        global $DB;
        $accountid = get_config('block_motrain', 'accountid');
        return $DB->record_exists('block_motrain_teammap', ['accountid' => $accountid]);
    }

    /**
     * Whether admins can earn.
     *
     * @return bool
     */
    public function is_admin_earning_enabled() {
        return (bool) get_config('block_motrain', 'adminscanearn');
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
     * Whether we update player's metadata when it changes.
     *
     * @return bool
     */
    public function is_player_metadata_sync_enabled() {
        return true;
    }

    /**
     * Whether the plugin is paused.
     *
     * When paused, the plugin will ignore any potential coin awards, and will not
     * push players to the dashboard. Essentially, it will prevent write operations
     * from happening.
     *
     * The plugin can be paused while it is being setup, or during a migration.
     *
     * @return bool
     */
    public function is_paused() {
        return (bool) get_config('block_motrain', 'ispaused');
    }

    /**
     * Whether the user is a player.
     *
     * A user is considered a player when they meet the conditions to become
     * one, not necessarily when have a mapping for said user. This is useful
     * to determine whether a user is allowed to receive coins beyond checking
     * the permission.
     *
     * @return bool
     */
    public function is_player($userid) {

        // Guests and admins are not players, unless admins can earn.
        if (!$userid || isguestuser($userid) || (!$this->is_admin_earning_enabled() && is_siteadmin($userid))) {
            return false;
        }

        // The user must belong to a team.
        return (bool) $this->get_team_resolver()->get_team_id_for_user($userid);
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
     * Make the client.
     *
     * This should not attach any observer.
     *
     * @return client The client.
     */
    protected function make_client() {
        $apikey = get_config('block_motrain', 'apikey');
        $apihost = get_config('block_motrain', 'apihost');
        $accountid = get_config('block_motrain', 'accountid');
        return new client($apihost, $apikey, $accountid);
    }

    /**
     * Observe failed requests.
     *
     * @param client_exception $e The exception.
     */
    public function observe_failed_request(client_exception $e) {
        // Automatically disable the plugin when the API reports that it is not authenticated, or when
        // API usage has been locked. The latter can happen when the account is in an invalid state, and
        // should no longer interact with the API at all.
        if ($e instanceof api_error && $e->get_http_code() === 401) {
            debugging('Disabling block_motrain plugin due to authentication issue with API.', DEBUG_DEVELOPER);
            set_config('isenabled', false, 'block_motrain');
        } else if ($e instanceof api_error && $e->get_http_code() === 423) {
            debugging('Disabling block_motrain plugin: API locked.', DEBUG_DEVELOPER);
            set_config('isenabled', false, 'block_motrain');
        }
    }

    /**
     * Require permission to award coins.
     *
     * @throws \required_capability_exception
     */
    public function require_award_coins() {
        require_capability('block/motrain:awardcoins', context_system::instance());
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
     * Require enabled.
     *
     * @throws \moodle_exception
     */
    public function require_not_paused() {
        if ($this->is_paused()) {
            throw new \moodle_exception('pluginispaused', 'block_motrain');
        }
    }

    /**
     * Require the user to be a player.
     *
     * @param int $userid The user ID.
     * @throws \moodle_exception
     */
    public function require_player($userid) {
        if (!$this->is_player($userid)) {
            throw new \moodle_exception('usernotplayer', 'block_motrain');
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

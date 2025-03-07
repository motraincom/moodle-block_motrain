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
use block_motrain\local\level_proxy;
use block_motrain\local\message_dealer;
use block_motrain\local\metadata_reader;
use block_motrain\local\player_mapper;
use block_motrain\local\purchase_proxy;
use block_motrain\local\team_resolver;
use block_motrain\local\user_pusher;
use block_motrain\task\adhoc_queue_cohort_members_for_push;
use cache;
use context;
use context_system;
use core_component;
use core_user;
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
    /** @var level_proxy|null The level proxy. */
    protected $levelproxy;
    /** @var message_dealer|null The message dealder. */
    protected $messagedealer;
    /** @var metadata_reader|null The metadata reader. */
    protected $metadatareader;
    /** @var player_mapper|null The player mapper. */
    protected $playermapper;
    /** @var purchase_proxy|null The purchase proxy. */
    protected $purchaseproxy;
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
     * Managing means seeing the block, and navigating to its page, and configuring it.
     */
    public function can_manage($userid = null) {
        return has_capability('block/motrain:manage', context_system::instance(), $userid);
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
     * Calculate tickets auto earned from coins.
     *
     * @return int
     */
    public function calculate_tickets_auto_earned_from_coins($coins) {
        if ($coins <= 0) {
            return 0;
        }

        $autoearnpc = $this->get_metadata_reader()->get_automatic_ticket_earning_percentage();
        if ($autoearnpc <= 0) {
            return 0;
        }

        $multiplier = $autoearnpc / 100;
        if ($autoearnpc == 33) {
            $multiplier = 1/3;
        } else if ($autoearnpc == 66) {
            $multiplier = 2/3;
        }

        return floor($coins * $multiplier);
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
     * This is deprecated as it provides an image that used to be defined in the admin settings, and fails
     * to return the image from the dashboard. Instead, refer to the appearance settings, see renderer.
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
     * Get the level proxy.
     *
     * @return level_proxy
     */
    public function get_level_proxy() {
        if (!$this->levelproxy) {
            $this->levelproxy = new level_proxy($this);
        }
        return $this->levelproxy;
    }

    /**
     * Get the message dealer.
     *
     * @return message_dealer
     */
    public function get_message_dealer() {
        if (!$this->messagedealer) {
            $this->messagedealer = new message_dealer();
        }
        return $this->messagedealer;
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
            // When local team management is enabled, we enable both of these.
            $this->playermapper->set_accept_foreign_teams($this->is_local_team_management_enabled());
            $this->playermapper->set_allow_team_change($this->is_local_team_management_enabled());
        }
        return $this->playermapper;
    }

    /**
     * Get the purchase proxy.
     *
     * @return purchase_proxy
     */
    public function get_purchase_proxy() {
        if (!$this->purchaseproxy) {
            $this->purchaseproxy = new purchase_proxy($this);
        }
        return $this->purchaseproxy;
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
     * Get the webhook processor.
     *
     * @return local\webhook\processor
     */
    public function get_webhook_processor() {
        return new local\webhook\processor($this);
    }

    /**
     * Get the last time a valid webhook was encountered.
     *
     * @return int Zero means never.
     */
    public function get_last_webhook_hit() {
        return (int) get_config('block_motrain', 'webhooklasthit');
    }

    /**
     * Whether user has brava enabled.
     *
     * @param int $userid The user ID.
     * @return bool
     */
    public function has_brava_enabled($userid) {
        $teamid = $this->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid && !$this->can_manage($userid)) {
            return false;
        }

        $metadata = $this->get_metadata_reader();
        if ($metadata->has_brava_enabled_in_team($teamid)) {
            return true;
        }

        if (!$this->can_manage($userid)) {
            return false;
        }

        return $metadata->has_brava_enabled_in_account();
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
     * Whether store elements are enabled.
     *
     * @return bool
     */
    public function has_store() {
        return $this->get_metadata_reader()->has_store();
    }

    /**
     * Whether user has tickets enabled.
     *
     * @param int $userid The user ID.
     * @return bool
     */
    public function has_tickets_enabled($userid) {
        $teamid = $this->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid && !$this->can_manage($userid)) {
            return false;
        }

        $metadata = $this->get_metadata_reader();
        if ($metadata->has_tickets_enabled_in_team($teamid)) {
            return true;
        }

        if (!$this->can_manage($userid)) {
            return false;
        }

        return $metadata->has_tickets_enabled_in_account();
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
     * Whether local team management is enabled.
     *
     * @return bool
     */
    public function is_local_team_management_enabled() {
        return (bool) get_config('block_motrain', 'localteammgmt');
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
     * Whether we send notifications locally.
     *
     * @return bool
     */
    public function is_sending_local_notifications_enabled() {
        return (bool) get_config('block_motrain', 'sendlocalnotifications');
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
     * Whether we are using Totara.
     *
     * @return bool
     */
    public function is_totara() {
        return array_key_exists('totara', core_component::get_plugin_types());
    }

    /**
     * Whether we are using cohorts.
     *
     * @return bool
     */
    public function is_using_cohorts() {
        $cfg = get_config('block_motrain', 'usecohorts');
        if ($cfg === false) {
            return true; // The default value is true.
        }
        return (bool) $cfg;
    }

    /**
     * Whether we are using cohorts.
     *
     * @return bool
     */
    public function is_webhook_connected() {
        $id = get_config('block_motrain', 'webhookid');
        $secret = get_config('block_motrain', 'webhooksecret');
        return !empty($id) && !empty($secret);
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

        $client = new client($apihost, $apikey, $accountid);
        $client->set_is_totara($this->is_totara());

        return $client;
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
     * Require view permissions.
     *
     * @throws \required_capability_exception
     */
    public function require_manage() {
        require_capability('block/motrain:manage', context_system::instance());
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
     * Setup the webhook.
     */
    public function setup_webhook() {
        $client = $this->get_client();
        $webhookid = get_config('block_motrain', 'webhookid');

        $webhookurl = new moodle_url('/blocks/motrain/webhook.php');
        $webhookdata = [
            'url' => $webhookurl->out(false),
            'description' => 'Integration with Moodle (block_motrain)',
            'event_types' => [
                'redemption.requestAccepted',
                'redemption.selfCompleted',
                'redemption.shippingOrderSubmitted',
                'redemption.voucherClaimed',
                'user.auctionWon',
                'user.leveledUp',
                'user.manuallyAwardedCoins',
                'user.raffleWon',
                'user.sweepstakesWon',
            ]
        ];

        if (!empty($webhookid)) {
            $client->update_webhook($webhookid, $webhookdata);
            return;
        }

        $webhook = $client->create_webhook($webhookdata);

        set_config('webhookid', $webhook->id, 'block_motrain');
        set_config('webhooksecret', $webhook->secret, 'block_motrain');
    }

    /**
     * Unset the webhook.
     */
    public function unset_webhook() {
        $webhookid = get_config('block_motrain', 'webhookid');

        if ($this->is_enabled()) {
            try {
                $this->get_client()->delete_webhook($webhookid);
            } catch (\moodle_exception $e) {
                debugging(get_string('errorwhiledisconnectingwebhook', 'block_motrain', $e->getMessage()), DEBUG_ALL);
            }
        }

        unset_config('webhookid', 'block_motrain');
        unset_config('webhooksecret', 'block_motrain');
        unset_config('webhooklasthit', 'block_motrain');
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

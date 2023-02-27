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
 * Balance proxy.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use block_motrain\manager;
use cache;

defined('MOODLE_INTERNAL') || die();

/**
 * Balance proxy.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class balance_proxy {

    /** @var cache The cache. */
    protected $coinscache;
    /** @var manager The manager. */
    protected $manager;

    /**
     * Constructor.
     *
     * @param manager The manager.
     */
    public function __construct(manager $manager) {
        $this->manager = $manager;
        $this->coinscache = cache::make('block_motrain', 'coins');
    }

    /**
     * Get the balance.
     *
     * @param object|int The user, or its ID.
     * @return int
     */
    public function get_balance($userorid) {
        return $this->get_player_coins($userorid)->coins;
    }

    /**
     * Get the total earned.
     *
     * @param object|int The user, or its ID.
     * @return int
     */
    public function get_total_earned($userorid) {
        return $this->get_player_coins($userorid)->coins_earned_lifetime;
    }

    /**
     * Get the coins from server.
     *
     * @param int|object $userorid The user, or its ID.
     * @return object With coins, and coins_earned_lifetime.
     */
    protected function get_player_coins($userorid) {
        $default = (object) ['coins' => 0, 'coins_earned_lifetime' => 0];
        $manager = $this->manager;

        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }

        if (!$manager->is_enabled() || $manager->is_paused()) {
            return $default;
        }

        $teamid = $manager->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid) {
            return $default;
        }

        $playerid = $manager->get_player_mapper()->get_player_id($userorid, $teamid);
        if (!$playerid) {
            return $default;
        }

        try {
            $player = $manager->get_client()->get_player($playerid);
            return (object) [
                'coins' => (int) ($player->coins ?? 0),
                'coins_earned_lifetime' => (int) ($player->coins_earned_lifetime ?? 0),
            ];
        } catch (api_error $e) {
            return $default;
        } catch (client_exception $e) {
            return $default;
        }
    }

    /**
     * Get the coins from server.
     *
     * @param int|object $userorid The user, or its ID.
     * @return object With coins, and coins_earned_lifetime.
     */
    protected function get_player_coins_cached($userorid) {
        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }
        $userid = (int) $userid;

        if (($data = $this->coinscache->get($userid)) === false) {
            $data = $this->get_player_coins($userorid);
            $this->coinscache->set($userid, $data);
        }

        return $data;
    }

    /**
     * Invalidate the balance for all users.
     */
    public function invalidate_all() {
        if (method_exists($this->coinscache, 'purge')) {
            $this->coinscache->purge();
        }
    }

    /**
     * Invalidate the balance for user.
     *
     * @param int $userid The user ID.
     */
    public function invalidate_balance($userid) {
        return $this->coinscache->delete((int) $userid);
    }

    /**
     * Set the balance cache.
     *
     * @param int $userid The user ID.
     * @param int $coins The coins.
     * @deprecated Do not use.
     */
    public function set_balance($userid, $coins) {
        throw new \coding_exception('Deprecated function.');
    }

}

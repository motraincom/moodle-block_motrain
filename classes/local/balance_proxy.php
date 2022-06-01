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
        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }

        if (($coins = $this->coinscache->get($userid)) === false) {
            $coins = $this->get_remote_balance($userorid);
            $this->set_balance($userid, $coins);
        }

        return $coins;
    }

    /**
     * Get the coins from server.
     *
     * @param int|object $userorid The user, or its ID.
     * @return int The coins.
     */
    protected function get_remote_balance($userorid) {
        $manager = $this->manager;

        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }

        if (!$manager->is_enabled() || $manager->is_paused()) {
            return 0;
        }

        $teamid = $manager->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid) {
            return 0;
        }

        $playerid = $manager->get_player_mapper()->get_player_id($userorid, $teamid);
        if (!$playerid) {
            return 0;
        }

        try {
            return $manager->get_client()->get_balance($playerid);
        } catch (api_error $e) {
            return 0;
        } catch (client_exception $e) {
            return 0;
        }
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
     */
    public function set_balance($userid, $coins) {
        $this->coinscache->set((int) $userid, $coins);
    }

}

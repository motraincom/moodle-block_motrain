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
 * Purchase proxy.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use block_motrain\manager;
use cache;

defined('MOODLE_INTERNAL') || die();

/**
 * Purchase proxy.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class purchase_proxy {

    /** @var cache The cache. */
    protected $cache;
    /** @var manager The manager. */
    protected $manager;

    /**
     * Constructor.
     *
     * @param manager The manager.
     */
    public function __construct(manager $manager) {
        $this->manager = $manager;
        $this->cache = cache::make('block_motrain', 'purchasemetadata');
    }

    /**
     * Count the purchases awaiting redemption.
     *
     * @param object|int The user, or its ID.
     * @return int The number of purchases awaiting redemption.
     */
    public function count_awaiting_redemption($userorid) {
        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }
        $userid = (int) $userid;

        $cachekey = "made-$userid";
        if (($data = $this->cache->get($cachekey)) === false) {
            $data = $this->fetch_awaiting_redemption_count($userorid);
            $this->cache->set($cachekey, $data);
        }

        return $data;
    }

    /**
     * Fetch the number of purchases awaiting redemption.
     *
     * @param object|int The user, or its ID.
     * @return int The number of purchases awaiting redemption.
     */
    protected function fetch_awaiting_redemption_count($userorid) {
        $userid = $userorid;
        if (is_object($userid)) {
            $userid = $userorid->id;
        }
        $userid = (int) $userid;

        $count = 0;
        $manager = $this->manager;

        if (!$manager->is_enabled() || $manager->is_paused()) {
            return $count;
        }

        $teamid = $manager->get_team_resolver()->get_team_id_for_user($userid);
        if (!$teamid) {
            return $count;
        }

        $playerid = $manager->get_player_mapper()->get_player_id($userorid, $teamid);
        if (!$playerid) {
            return $count;
        }

        try {
            $client = $this->manager->get_client();
            $count = $client->get_player_purchases_count($playerid, ['state' => 'made']);
        } catch (\moodle_exception $e) {
            $count = 0;
        }

        return $count;
    }

    /**
     * Invalidate the cache for all users.
     */
    public function invalidate_all() {
        if (method_exists($this->cache, 'purge')) {
            $this->cache->purge();
        }
    }

    /**
     * Invalidate the cache for user.
     *
     * @param int $userid The user ID.
     */
    public function invalidate_user($userid) {
        $keys = [
            "made-$userid",
        ];
        foreach ($keys as $key) {
            return $this->cache->delete($key);
        }
    }

}

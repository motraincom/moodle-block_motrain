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
 * Metadata reader.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use block_motrain\manager;
use cache;
use core_user;

defined('MOODLE_INTERNAL') || die();

/**
 * Metadata reader.
 *
 * This class is used to efficiently read metadata associated with the
 * account that requires interaction with the API. The results will be
 * cached.
 *
 * If the information you are looking for should be invalidated on a
 * regular basis, then it is probably not supposed to be added here. The
 * metadata available here is not expected to change often.
 *
 * This class should also handle most exceptions.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metadata_reader {

    /** @var cache The cache. */
    protected $cache;
    /** @var manager The manager. */
    protected $manager;

    /**
     * Constructor.
     *
     * @param manager $manager The manager.
     */
    public function __construct(manager $manager) {
        $this->cache = cache::make('block_motrain', 'metadata');
        $this->manager = $manager;
    }

    /**
     * Get the account.
     *
     * @return object|null
     */
    protected function get_account() {
        $key = 'account';
        if (($val = $this->cache->get($key)) === false) {
            try {
                $val = $this->manager->get_client()->get_account();
            } catch (client_exception $e) {
                $val = null;
            }
            $this->cache->set($key, $val);
        }
        return $val;
    }

    /**
     * Get the automatic earning percentage of tickets.
     *
     * @return int
     */
    public function get_automatic_ticket_earning_percentage() {
        return (int) $this->get_account()->tickets_auto_earn ?? 0;
    }

    /**
     * Get the branding.
     *
     * @return object|null
     */
    protected function get_branding() {
        return $this->get_account()->branding ?? null;
    }

    /**
     * Get the icon double URL.
     *
     * @return string|null
     */
    public function get_icon_double_url() {
        $branding = $this->get_branding();
        return $branding->icon_double ?? null;
    }

    /**
     * Get the item.
     *
     * @param string $itemid The item ID.
     * @return object|null
     */
    protected function get_item($itemid) {
        $key = 'item_' . $itemid;
        if (($val = $this->cache->get($key)) === false) {
            try {
                $val = $this->manager->get_client()->get_item($itemid);
            } catch (client_exception $e) {
                $val = null;
            }
            $this->cache->set($key, $val);
        }
        return $val;
    }

    /**
     * Get the item name.
     *
     * @param string $itemid The item ID.
     * @return string
     */
    public function get_item_name($itemid) {
        return $this->get_item($itemid)->name ?? 'Unknown item';
    }

    /**
     * Get the item redemption message.
     *
     * @param string $itemid The item ID.
     * @return string|null
     */
    public function get_item_redemption_message($itemid) {
        return $this->get_item($itemid)->redemption->message ?? null;
    }

    /**
     * Get the levels.
     *
     * @return object[]
     */
    public function get_levels() {
        $key = 'levels';
        if (($val = $this->cache->get($key)) === false) {
            try {
                $resp = $this->manager->get_client()->get_account_levels();
                $val = $resp->levels ?? [];
            } catch (client_exception $e) {
                $val = [];
            }
            $this->cache->set($key, $val);
        }
        return $val;
    }

    /**
     * Get the tickets icon URL.
     *
     * @return object|null
     */
    protected function get_team($teamid) {
        $key = 'team_' . $teamid;
        if (($val = $this->cache->get($key)) === false) {
            try {
                $val = $this->manager->get_client()->get_team($teamid);
            } catch (client_exception $e) {
                $val = null;
            }
            $this->cache->set($key, $val);
        }
        return $val;
    }

    /**
     * Whether store elements are enabled.
     *
     * @return bool
     */
    public function has_store() {
        return $this->get_branding()->has_store ?? true;
    }

    /**
     * Get the tickets icon URL.
     *
     * @return string|null
     */
    public function get_tickets_icon_url() {
        $branding = $this->get_branding();
        return $branding->tickets_icon ?? null;
    }

    /**
     * Whether tickets are enabled.
     *
     * @param string $teamid The team ID.
     * @return bool
     */
    public function has_tickets_enabled_in_account() {
        return $this->get_account()->tickets_enabled ?? false;
    }

    /**
     * Whether tickets are enabled.
     *
     * @param string $teamid The team ID.
     * @return bool
     */
    public function has_tickets_enabled_in_team($teamid) {
        return $this->get_team($teamid)->tickets_enabled ?? false;
    }

    /**
     * Is the account leaderboard enabled.
     *
     * @return bool
     */
    public function is_account_leaderboard_enabled() {
        $leaderboards = $this->get_account()->leaderboards ?? [];
        foreach ($leaderboards as $leaderboard) {
            if ($leaderboard->id === 'svs') {
                return (bool) $leaderboard->enabled;
            }
        }
        return false;
    }

    /**
     * Is the team leaderboard enabled.
     *
     * @param string $teamid The team ID.
     * @return bool
     */
    public function is_team_leaderboard_enabled($teamid) {
        $leaderboards = $this->get_team($teamid)->leaderboards ?? [];
        foreach ($leaderboards as $leaderboard) {
            if ($leaderboard->id === 'individual') {
                return (bool) $leaderboard->enabled;
            }
        }
        return false;
    }

    /**
     * Is the leaderboard of any known team enabled.
     *
     * @return bool
     */
    public function is_any_known_team_leaderboard_enabled() {
        global $DB;

        $sql = 'accountid = ?';
        $params = [$this->manager->get_account_id()];
        if ($this->manager->is_using_cohorts()) {
            $sql .= ' AND cohortid > 0';
        } else {
            $sql .= ' AND cohortid <= 0';
        }

        $teamids = $DB->get_fieldset_select('block_motrain_teammap', 'DISTINCT teamid', $sql, $params);
        foreach ($teamids as $teamid) {
            if ($this->is_team_leaderboard_enabled($teamid)) {
                return true;
            }
        }
        return false;
    }

}

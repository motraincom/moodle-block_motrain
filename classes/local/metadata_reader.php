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
     * Is the account leaderboard enabled.
     *
     * @return bool
     */
    public function is_account_leaderboard_enabled() {
        $key = 'account_leaderboard_enabled';
        if (($val = $this->cache->get($key)) === false) {
            try {
                $val = (int) $this->manager->get_client()->is_account_leaderboard_enabled();
            } catch (client_exception $e) {
                $val = 0;
            }
            $this->cache->set($key, $val);
        }
        return (bool) $val;
    }

    /**
     * Is the team leaderboard enabled.
     *
     * @param string $teamid The team ID.
     * @return bool
     */
    public function is_team_leaderboard_enabled($teamid) {
        $key = 'team_leaderboard_enabled:' . $teamid;
        if (($val = $this->cache->get($key)) === false) {
            try {
                $val = (int) $this->manager->get_client()->is_team_leaderboard_enabled($teamid);
            } catch (client_exception $e) {
                $val = 0;
            }
            $this->cache->set($key, $val);
        }
        return (bool) $val;
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

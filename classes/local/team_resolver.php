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
 * Team resolver.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Team resolver.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class team_resolver {

    /** @var string The account ID. */
    protected $accountid;
    /** @var string|null|false The global team ID. */
    protected $globalteamid;
    /** @var bool Whether we're using cohorts. */
    protected $isusingcohorts = true;

    /**
     * Constructor.
     *
     * @param bool $isusingcohorts Whether we're using cohorts.
     * @param string $accountid The account ID.
     */
    public function __construct($isusingcohorts, $accountid) {
        $this->isusingcohorts = $isusingcohorts;
        $this->accountid = $accountid;
    }

    /**
     * Get the global team ID.
     *
     * @return string|null
     */
    protected function get_global_team_id() {
        global $DB;
        if ($this->globalteamid === null) {
            $this->globalteamid = $DB->get_field('block_motrain_teammap', 'teamid', ['cohortid' => -1]);
        }
        return $this->globalteamid ? $this->globalteamid : null;
    }

    /**
     * Get the team for the user.
     *
     * @param int $userid The user ID.
     */
    public function get_team_id_for_user($userid) {
        global $DB;

        if (!$this->isusingcohorts) {
            return $this->get_global_team_id();
        }

        $sql = 'SELECT t.teamid
                  FROM {cohort_members} cm
                  JOIN {block_motrain_teammap} t
                    ON t.cohortid = cm.cohortid
                 WHERE cm.userid = :userid
              ORDER BY cm.cohortid ASC';
        return $DB->get_field_sql($sql, ['userid' => $userid], IGNORE_MULTIPLE);
    }

}
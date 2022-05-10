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

use block_motrain\local\collection_strategy;
use block_motrain\local\player_mapper;
use block_motrain\local\team_resolver;

defined('MOODLE_INTERNAL') || die();

/**
 * Manager.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var client|null The client. */
    protected $client;
    /** @var collection_strategy|null The collection strategy. */
    protected $collectionstrategy;
    /** @var team_resolver|null The team resolver. */
    protected $teamresolver;
    /** @var static The singleton. */
    protected static $instance;

    public function __construct() {
    }

    public function get_account_id() {
        return get_config('block_motrain', 'accountid');
    }

    public function get_client() {
        if (!isset($this->client)) {
            $apikey = get_config('block_motrain', 'apikey');
            $apihost = get_config('block_motrain', 'apihost');
            $accountid = get_config('block_motrain', 'accountid');
            $this->client = new client($apihost, $apikey, $accountid);
        }
        return $this->client;
    }

    /**
     * Get the collection strategy.
     *
     * @return collection_strategy
     */
    public function get_collection_strategy() {
        if (!$this->collectionstrategy) {
            $accountid = $this->get_account_id();
            $teamresolver = new team_resolver($this->is_using_cohorts(), $accountid);
            $playermapper = new player_mapper($this->get_client(), $accountid);
            $this->collectionstrategy = new collection_strategy($teamresolver, $playermapper);
        }
        return $this->collectionstrategy;
    }

    public function get_global_team_association() {
        global $DB;
        $accountid = get_config('block_motrain', 'accountid');
        return $DB->get_record('block_motrain_team', ['accountid' => $accountid, 'cohortid' => -1]);
    }

    public function has_team_associations() {
        global $DB;
        $accountid = get_config('block_motrain', 'accountid');
        return $DB->record_exists('block_motrain_team', ['accountid' => $accountid]);
    }

    public function is_setup() {
        $apikey = get_config('block_motrain', 'apikey');
        $apihost = get_config('block_motrain', 'apihost');
        $accountid = get_config('block_motrain', 'accountid');
        return !empty($apikey) && !empty($apihost) && !empty($accountid);
    }

    public function is_using_cohorts() {
        return (bool) get_config('block_motrain', 'usecohorts');
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
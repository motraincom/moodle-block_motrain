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
 * Client.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain;

use curl;
use local_mootivated\local\lang_reason;
use moodle_exception;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Client.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client {

    /** @var string The API key. */
    protected $apikey;
    /** @var string The API host. */
    protected $apihost;
    /** @var string The account ID. */
    protected $accountid;

    /**
     * Constructor.
     *
     * @param string $apihost The API host.
     * @param string $apikey The API key.
     * @param string $accountid The account ID.
     */
    public function __construct($apihost, $apikey, $accountid) {
        $this->apihost = $apihost;
        $this->apikey = $apikey;
        $this->accountid = $accountid;
        $this->lang = current_language();
    }

    public function add_coins($playerid, $coins, lang_reason $reason = null) {
        $data = ['coins' => $coins];
        if ($reason) {
            $data['reason'] = [
                'string' => $reason->get_string(),
                'args' => $reason->get_args()
            ];
        }
        return $this->post('/v2/users/' . $playerid . '/balance', $data);
    }

    public function create_player($teamid, $data) {
        return $this->post('/v2/teams/' . $teamid . '/users', $data);
    }

    public function get_player_by_email($teamid, $email) {
        $resp = $this->get('/v2/teams/' . $teamid . '/users', ['email' => $email]);
        if (!empty($resp)) {
            return $resp[0];
        }
        return null;
    }

    public function get_teams() {
        return $this->get('/v2/accounts/' . $this->accountid . '/teams');
    }

    protected function get($uri, $params = null) {
        return $this->request('GET', $uri, $params);
    }

    protected function post($uri, $data = null) {
        return $this->request('POST', $uri, $data);
    }

    protected function request($method, $uri, $data = null) {
        $method = strtoupper($method);

        $curl = new curl();
        $curl->setHeader('Content-Type: application/json');
        $curl->setHeader('Accept-Language', $this->lang);
        $curl->setHeader('Authorization: Bearer ' . $this->apikey);

        if ($method === 'POST') {
            $url = new moodle_url($this->apihost . $uri);
            $response = $curl->post($url, $data ? json_encode($data) : '');
        } else if ($method === 'GET') {
            $url = new moodle_url($this->apihost . $uri, $data);
            $response = $curl->get($url->out(false));
        }

        if ($curl->error) {
            throw new moodle_exception('request_error', 'block_motrain', '', null, $response);
        } else if ($curl->info['http_code'] >= 300) {
            throw new moodle_exception('request_failed', 'block_motrain', '', null, $response);
        }

        $data = null;
        if ($curl->info['http_code'] !== 204) {
            $data = json_decode($response);
            if ($data === null) {
                throw new moodle_exception('JSON decode failed', 'block_motrain', '', null, $response);
            }
        }

        return $data;
    }

}
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

use block_motrain\local\api_error;
use block_motrain\local\client_exception;
use block_motrain\local\reason\lang_reason;
use curl;
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
    /** @var object An observer. */
    protected $observer;
    /** @var string The language. */
    protected $lang;

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
                'string' => $reason->get_string()
            ];
            if ($reason->get_args() !== null) {
                $data['reason']['args'] = $reason->get_args();
            }
        }
        return $this->post('/v2/users/' . $playerid . '/balance', $data);
    }

    public function complete_redemption($redemptionid) {
        return $this->put('/v2/redemptions/' . $redemptionid . '/complete');
    }

    public function create_player($teamid, $data) {
        return $this->post('/v2/teams/' . $teamid . '/users', $data);
    }

    public function create_webhook($data) {
        return $this->post('/v2/accounts/' . $this->accountid . '/webhooks', $data);
    }

    public function delete_webhook($webhookid) {
        return $this->delete('/v2/accounts/' . $this->accountid . '/webhooks/' . $webhookid);
    }

    public function get_account() {
        return $this->get('/v2/accounts/' . $this->accountid);
    }

    public function get_account_branding() {
        return $this->get('/v2/accounts/' . $this->accountid . '/branding');
    }

    public function get_account_levels() {
        return $this->get('/v2/accounts/' . $this->accountid . '/levels');
    }

    public function get_balance($playerid) {
        $resp = $this->get('/v2/users/' . $playerid . '/balance');
        return $resp->coins;
    }

    public function get_item($itemid) {
        $resp = $this->get('/v2/items/' . $itemid);
        return $resp;
    }

    public function get_player($playerid) {
        return $this->get('/v2/users/' . $playerid);
    }

    public function get_player_by_email($teamid, $email) {
        $resp = $this->get('/v2/teams/' . $teamid . '/users', ['email' => $email]);
        if (!empty($resp)) {
            return $resp[0];
        }
        return null;
    }

    public function get_player_redemptions($playerid, $filters) {
        $resp = $this->request_advanced('GET', '/v2/users/' . $playerid . '/redemptions', $filters);
        if (!empty($resp->headers['Link']) || !empty($resp->headers['link'])) {
            debugging('API returned paginated results, but we only return the first page.', DEBUG_DEVELOPER);
        }
        return $this->decode_json_response($resp);
    }

    public function get_redemption($redemptionid) {
        $resp = $this->request_advanced('GET', '/v2/redemptions/' . $redemptionid);
        return $this->decode_json_response($resp);
    }

    public function get_store_login_url($playerid, $landingpage = null) {
        $params = null;
        if (in_array($landingpage, ['shop', 'info', 'leaderboards', 'purchases', 'activity'])) {
            $params = ['landing_page' => $landingpage];
        }
        $resp = $this->post('/v2/users/' . $playerid . '/store_login_url', $params);
        return $resp->url;
    }

    public function get_teams() {
        return $this->get('/v2/accounts/' . $this->accountid . '/teams');
    }

    public function is_account_leaderboard_enabled() {
        try {
            $this->head('/v2/accounts/' . $this->accountid . '/leaderboard');
        } catch (api_error $e) {
            if ($e->get_http_code() === 404) {
                return false;
            }
            throw $e;
        }
        return true;
    }

    public function is_team_leaderboard_enabled($teamid) {
        try {
            $this->head('/v2/teams/' . $teamid . '/leaderboard');
        } catch (api_error $e) {
            if ($e->get_http_code() === 404) {
                return false;
            }
            throw $e;
        }
        return true;
    }

    public function update_player($playerid, $data) {
        return $this->patch('/v2/users/' . $playerid, $data);
    }

    public function update_webhook($webhookid, $data) {
        return $this->patch('/v2/accounts/' . $this->accountid . '/webhooks/' . $webhookid, $data);
    }

    protected function delete($uri, $params = null) {
        return $this->request('DELETE', $uri, $params);
    }

    protected function head($uri, $params = null) {
        return $this->request('HEAD', $uri, $params);
    }

    protected function get($uri, $params = null) {
        return $this->request('GET', $uri, $params);
    }

    protected function patch($uri, $data = null) {
        return $this->request('PATCH', $uri, $data);
    }

    protected function post($uri, $data = null) {
        return $this->request('POST', $uri, $data);
    }

    protected function put($uri, $data = null) {
        return $this->request('PUT', $uri, $data);
    }

    protected function decode_json_response($result) {
        $data = json_decode($result->response);
        if ($data === null) {
            throw new client_exception('json_expected', $result->curl, $result->response);
        }
        return $data;
    }

    protected function request($method, $uri, $data = null) {
        $result = $this->request_advanced($method, $uri, $data);

        $data = null;
        if ($result->http_code !== 204) {
            $data = $this->decode_json_response($result);
        }

        return $data;
    }

    protected function request_advanced($method, $uri, $data = null) {
        $method = strtoupper($method);

        $curl = new curl();
        $curl->setHeader('Content-Type: application/json');
        $curl->setHeader('Accept-Language', $this->lang);
        $curl->setHeader('Authorization: Bearer ' . $this->apikey);

        if ($method === 'POST') {
            $url = new moodle_url($this->apihost . $uri);
            $response = $curl->post($url, $data ? json_encode($data) : '');
        } else if ($method === 'PATCH') {
            $url = new moodle_url($this->apihost . $uri);
            $response = $curl->post($url, $data ? json_encode($data) : '', [
                'CURLOPT_CUSTOMREQUEST' => 'PATCH'
            ]);
        } else if ($method === 'PUT') {
            $url = new moodle_url($this->apihost . $uri);
            $response = $curl->put($url, $data ? json_encode($data) : '');
        } else if ($method === 'GET') {
            $url = new moodle_url($this->apihost . $uri, $data);
            $response = $curl->get($url->out(false));
        } else if ($method === 'HEAD') {
            $url = new moodle_url($this->apihost . $uri, $data);
            $response = $curl->head($url->out(false));
        } else if ($method === 'DELETE') {
            $url = new moodle_url($this->apihost . $uri, $data);
            $response = $curl->delete($url->out(false));
        }

        try {
            if ($curl->error) {
                throw new client_exception('request_error', $curl, $response);
            } else if ($curl->info['http_code'] >= 300) {
                throw new api_error($curl, $response);
            }
        } catch (client_exception $e) {
            if ($this->observer && method_exists($this->observer, 'observe_failed_request')) {
                try {
                    $this->observer->observe_failed_request($e);
                } catch (\Exception $observerexception) {
                    debugging('Client observer threw an exception: ' . $observerexception->getMessage(), DEBUG_DEVELOPER);
                }
            }
            throw $e;
        }

        return (object) [
            'curl' => $curl,
            'response' => $response,
            'http_code' => $curl->info['http_code'],
            'headers' => $curl->getResponse(),
        ];
    }

    public function set_observer($observer) {
        $this->observer = $observer;
    }

}

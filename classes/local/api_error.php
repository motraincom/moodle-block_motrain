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
 * API error.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;
defined('MOODLE_INTERNAL') || die();

/**
 * API error.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_error extends client_exception {

    /** @var mixed The response decoded. */
    protected $responsedecoded;

    /**
     * Constructor.
     *
     * @param curl $curl The curl object post request.
     * @param string $response The response from the server.
     */
    public function __construct($curl, $response) {
        $this->responsedecoded = json_decode($response);
        parent::__construct('api_error', $curl, $response);
    }

    /**
     * Get the API error code.
     *
     * @return string
     */
    public function get_error_code() {
        return is_object($this->responsedecoded) ? $this->responsedecoded->code : 'UNKNOWN_ERROR';
    }

    /**
     * Get the API error message.
     *
     * @return string
     */
    public function get_error_message() {
        return is_object($this->responsedecoded) ? $this->responsedecoded->message : 'Unknown error';
    }

}

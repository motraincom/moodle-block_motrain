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

namespace block_motrain\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Test server controls.
 *
 * @package    block_motrain
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class api_server_test_client extends \block_motrain\client {

    /** @var self|null The instance. */
    protected static $instance;

    /**
     * Constructor.
     */
    protected function __construct() {
        parent::__construct(
            get_config('block_motrain', 'apihost'),
            get_config('block_motrain', 'apikey'),
            get_config('block_motrain', 'accountid')
        );
    }


    /**
     * Restore the baseline.
     */
    public function before_scenario(): void {
        $this->post('/_test/before');
    }

    /**
     * Configure the account.
     *
     * @param array $data Account settings.
     */
    public function configure_account(array $data): void {
        $this->patch('/_test/accounts/' . $this->accountid, $data);
        \cache_helper::purge_by_definition('block_motrain', 'metadata');
    }

    /**
     * Get the instance.
     *
     * @return self
     */
    public static function instance(): self {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reset the instance.
     */
    public static function reset_instance(): void {
        self::$instance = null;
    }
}

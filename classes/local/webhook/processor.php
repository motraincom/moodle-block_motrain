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
 * Webhook processor.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local\webhook;

use block_motrain\manager;
use core_user;

defined('MOODLE_INTERNAL') || die();

/**
 * Webhook processor.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class processor {

    /** @var manager The manager. */
    protected $manager;

    /**
     * Constructor.
     *
     * @param manager $manager The manager.
     */
    public function __construct(manager $manager) {
        $this->manager = $manager;
    }

    /**
     * Process a webhook.
     *
     * @param string $type The event type.
     * @param object $payload The payload.
     */
    public function process_webhook($type, $payload) {

        // Validate the account ID.
        if ($this->manager->get_account_id() !== $payload->account_id ?? '-unknown-') {
            throw new \moodle_exception('accountidmismatch', 'block_motrain');
        }

        set_config('webhooklasthit', time(), 'block_motrain');

        $supported = [
            'redemption.requestAccepted',
            'redemption.selfCompleted',
            'user.auctionWon',
            'user.manuallyAwardedCoins',
            'user.raffleWon'
        ];

        if (!in_array($type, $supported)) {
            return;
        }

        // Obtain the local user.
        $playermapper = $this->manager->get_player_mapper();
        $userid = $playermapper->get_local_user_id($payload->user_id ?? 0);
        if (!$userid) {
            return;
        }
        $user = core_user::get_user($userid);
        if (!$user || !core_user::is_real_user($user->id)) {
            return;
        }

        // Send a notification.
        $this->manager->send_notification($user, 'Congrats', 'That was amazing!');
    }

}

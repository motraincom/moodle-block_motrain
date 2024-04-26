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

use block_motrain\local\message_dealer;
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
     * Broadcast webhooks to other plugins.
     *
     * @param string $type The webhook type.
     * @param object $payload The webhook payload.
     */
    protected function broadcast_webhook($type, $payload) {
        $pluginsbytype = get_plugins_with_function('handle_block_motrain_webhook');
        foreach ($pluginsbytype as $plugintype => $plugins) {
            foreach ($plugins as $pluginname => $functionname) {
                $component = $plugintype . '_' . $pluginname;
                try {
                    component_callback($component, 'handle_block_motrain_webhook', [$type, $payload]);
                } catch (\Exception $e) {
                    debugging("Webhook handler $pluginname::$functionname failed with $type: " . $e->getMessage(), DEBUG_DEVELOPER);
                }

            }
        }
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

        $this->handle_local_notifications($type, $payload);
        $this->broadcast_webhook($type, $payload);
    }

    /**
     * Handle local notifications.
     *
     * @param string $type The type.
     * @param object $payload The payload.
     */
    protected function handle_local_notifications($type, $payload) {
        $supported = [
            'redemption.requestAccepted',
            'redemption.selfCompleted',
            'redemption.shippingOrderSubmitted',
            'redemption.voucherClaimed',
            'user.auctionWon',
            'user.manuallyAwardedCoins',
            'user.raffleWon'
        ];

        if (!in_array($type, $supported)) {
            return;
        } else if (!$this->manager->is_sending_local_notifications_enabled()) {
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

        // Prepare the notification.
        $code = null;
        $data = [];
        $metadatareader = $this->manager->get_metadata_reader();
        if ($type == 'redemption.requestAccepted') {
            $code = message_dealer::TYPE_REDEMPTION_REQUEST_ACCEPTED;
            $data = [
                'itemname' => $metadatareader->get_item_name($payload->item_id),
                'message' => $payload->message ?? '',
            ];

        } else if ($type == 'redemption.selfCompleted') {
            $code = message_dealer::TYPE_REDEMPTION_SELF_COMPLETED;
            $data = [
                'itemname' => $metadatareader->get_item_name($payload->item_id),
                'message' => $metadatareader->get_item_redemption_message($payload->item_id)
                    ?? get_string('noredemptionessagefound', 'block_motrain'),
            ];

        } else if ($type == 'redemption.shippingOrderSubmitted') {
            if (count($payload->items) === 1) {
                $item = $payload->items[0];
                $qtyprefix = $item->quantity > 1 ? "{$item->quantity}x " : '';
                $itemnames = $qtyprefix . $metadatareader->get_item_name($item->id);
            } else {
                $itemnames = implode(', ', array_map(function($item) use ($metadatareader) {
                    return "{$item->quantity}x " . $metadatareader->get_item_name($item->id);
                }, $payload->items));
            }
            $code = message_dealer::TYPE_REDEMPTION_SHIPPING_ORDER_SUBMITTED;
            $data = [
                'itemname' => $itemnames
            ];

        } else if ($type == 'redemption.voucherClaimed') {
            $code = message_dealer::TYPE_REDEMPTION_VOUCHER_CLAIMED;
            $data = [
                'itemname' => $metadatareader->get_item_name($payload->item_id),
                'vouchercode' => $payload->voucher_code,
            ];

        } else if ($type == 'user.auctionWon') {
            $code = message_dealer::TYPE_USER_AUCTION_WON;
            $data = [
                'itemname' => $metadatareader->get_item_name($payload->item_id)
            ];

        } else if ($type == 'user.manuallyAwardedCoins') {
            $code = message_dealer::TYPE_USER_MANUAL_AWARD;
            $data = [
                'coins' => $payload->coins,
                'message' => $payload->message ?? '-',
            ];

        } else if ($type == 'user.raffleWon') {
            $code = message_dealer::TYPE_USER_RAFFLE_WON;
            $data = [
                'itemname' => $metadatareader->get_item_name($payload->item_id),
            ];
        }

        $messagedealer = $this->manager->get_message_dealer();
        $template = $messagedealer->resolve_best_template($code, $user->lang);
        if (!$template) {
            return;
        }

        $messagedealer->send_notification($user, $template, $data);
    }
}

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
 * Message dealer.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use context_system;
use core_user;
use lang_string;

defined('MOODLE_INTERNAL') || die();

/**
 * Message dealer.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_dealer {

    const TYPE_REDEMPTION_REQUEST_ACCEPTED = 'redemption_request_accepted';
    const TYPE_REDEMPTION_SELF_COMPLETED = 'redemption_self_completed';
    const TYPE_REDEMPTION_SHIPPING_ORDER_SUBMITTED = 'redemption_shipping_order_submit';
    const TYPE_USER_AUCTION_WON = 'user_auction_won';
    const TYPE_USER_MANUAL_AWARD = 'user_manual_award';
    const TYPE_USER_RAFFLE_WON = 'user_raffle_won';
    const TYPE_USER_SWEEPSTAKES_WON = 'user_sweepstakes_won';

    /** @var string[] List of TYPE_* constant values. */
    protected $knowntypes;
    /** @var object[] Indexed by type. */
    protected $types;

    /**
     * Constructor.
     */
    public function __construct() {
        // We cannot remove types that may have been used in the past without guaranteeing that
        // existing data is migrated else there could be some unknown issues.
        $this->types = [
            static::TYPE_REDEMPTION_REQUEST_ACCEPTED => (object) [
                'code' => static::TYPE_REDEMPTION_REQUEST_ACCEPTED,
                'placeholders' => [
                    (object) [
                        'tag' => 'itemname',
                        'description' => new lang_string('placeholderitemname', 'block_motrain'),
                        'example' => new lang_string('placeholderitemnameexample', 'block_motrain')
                    ],
                    (object) [
                        'tag' => 'message',
                        'description' => new lang_string('placeholdermessage', 'block_motrain'),
                        'example' => new lang_string('placeholdermessageexample', 'block_motrain')
                    ],
                ],
                'name' => new lang_string('templatetyperedemptionrequestaccepted', 'block_motrain'),
            ],
            static::TYPE_REDEMPTION_SELF_COMPLETED => (object) [
                'code' => static::TYPE_REDEMPTION_SELF_COMPLETED,
                'placeholders' => [
                    (object) [
                        'tag' => 'itemname',
                        'description' => new lang_string('placeholderitemname', 'block_motrain'),
                        'example' => new lang_string('placeholderitemnameexample', 'block_motrain')
                    ],
                    (object) [
                        'tag' => 'message',
                        'description' => new lang_string('placeholderoptionalmessagefromadmin', 'block_motrain'),
                        'example' => new lang_string('placeholdermessageexample', 'block_motrain')
                    ],
                ],
                'name' => new lang_string('templatetyperedemptionselfcompleted', 'block_motrain'),
            ],
            static::TYPE_REDEMPTION_SHIPPING_ORDER_SUBMITTED => (object) [
                'code' => static::TYPE_REDEMPTION_SHIPPING_ORDER_SUBMITTED,
                'placeholders' => [
                    (object) [
                        'tag' => 'itemname',
                        'description' => new lang_string('placeholderitems', 'block_motrain'),
                        'example' => new lang_string('placeholderitemsexample', 'block_motrain')
                    ]
                ],
                'name' => new lang_string('templatetyperedemptionshippingordersubmitted', 'block_motrain'),
            ],
            static::TYPE_USER_AUCTION_WON => (object) [
                'code' => static::TYPE_USER_AUCTION_WON,
                'placeholders' => [
                    (object) [
                        'tag' => 'itemname',
                        'description' => new lang_string('placeholderitemname', 'block_motrain'),
                        'example' => new lang_string('placeholderitemnameexample', 'block_motrain')
                    ],
                ],
                'name' => new lang_string('templatetypeauctionwon', 'block_motrain'),
            ],
            static::TYPE_USER_MANUAL_AWARD => (object) [
                'code' => static::TYPE_USER_MANUAL_AWARD,
                'placeholders' => [
                    (object) [
                        'tag' => 'coins',
                        'description' => new lang_string('placeholdercoins', 'block_motrain'),
                        'example' => '100'
                    ],
                    (object) [
                        'tag' => 'message',
                        'description' => new lang_string('placeholderoptionalmessagefromadmin', 'block_motrain'),
                        'example' => new lang_string('placeholdermessageexample', 'block_motrain')
                    ],
                ],
                'name' => new lang_string('templatetypemanualaward', 'block_motrain'),
            ],
            static::TYPE_USER_RAFFLE_WON => (object) [
                'code' => static::TYPE_USER_RAFFLE_WON,
                'placeholders' => [
                    (object) [
                        'tag' => 'itemname',
                        'description' => new lang_string('placeholderitemname', 'block_motrain'),
                        'example' => new lang_string('placeholderitemnameexample', 'block_motrain')
                    ],
                ],
                'name' => new lang_string('templatetyperafflewon', 'block_motrain'),
            ],
            static::TYPE_USER_SWEEPSTAKES_WON => (object) [
                'code' => static::TYPE_USER_SWEEPSTAKES_WON,
                'placeholders' => [
                    (object) [
                        'tag' => 'itemname',
                        'description' => new lang_string('placeholderitemname', 'block_motrain'),
                        'example' => new lang_string('placeholderitemnameexample', 'block_motrain')
                    ],
                ],
                'name' => new lang_string('templatetypesweepstakeswon', 'block_motrain'),
            ],
        ];
        $this->knowntypes = array_keys($this->types);
    }

    /**
     * Whether the template can be deleted.
     *
     * @param object $template The template record.
     * @return bool
     */
    public function can_delete_template($template) {
        return !$this->is_a_default_template($template);
    }

    /**
     * Create the missing default templates.
     *
     * Adding new types will require this to be called as part of the upgrade mechanism.
     * Maybe we should set a flag upon upgrade and check for it to trigger the upgrade.
     */
    public function create_missing_default_templates() {
        global $DB;

        $existingtypes = $DB->get_fieldset_select('block_motrain_msgtpl', 'code', 'lang IS NULL', []);
        $missingtypes = array_diff($this->knowntypes, $existingtypes);

        foreach ($missingtypes as $type) {
            [$subject, $content] = $this->get_default_content($type);
            $record = (object) [
                'code' => $type,
                'lang' => null,
                'subject' => $subject,
                'content' => $content,
                'contentformat' => FORMAT_HTML,
                'enabled' => true,
            ];
            $DB->insert_record('block_motrain_msgtpl', $record);
        }
    }

    /**
     * Default content.
     *
     * These are hardcoded in English, they should be translated by the user.
     *
     * @param string $type The type.
     * @return array
     */
    protected function get_default_content($type) {
        if ($type === static::TYPE_REDEMPTION_REQUEST_ACCEPTED) {
            $subject = 'Great job! Your order has been approved';
            $content = markdown_to_html("Hi [firstname],\n\nCongratulations! Your [itemname] has been approved with "
                . "the following message:\n\n[message]");

        } else if ($type === static::TYPE_REDEMPTION_SELF_COMPLETED) {
            $subject = 'Nice work! Your item is available';
            $content = markdown_to_html("Congratulations!  Your [itemname] has been redeemed with "
                . "the following message:\n\n[message]");

        } else if ($type === static::TYPE_REDEMPTION_SHIPPING_ORDER_SUBMITTED) {
            $subject = 'Order processed';
            $content = markdown_to_html("It's time to start checking your mailbox! Your order "
                . "for [itemname] was successfully sent.");

        } else if ($type === static::TYPE_USER_AUCTION_WON) {
            $subject = 'You are the highest bidder! You win!';
            $content = markdown_to_html("Hi [firstname],\n\nCongratulations, you won! You are the winner of the [itemname] "
                . "auction. To get it, please go to \"Pending Orders\" and complete the order.");

        } else if ($type === static::TYPE_USER_MANUAL_AWARD) {
            $subject = 'You have been rewarded';
            $content = markdown_to_html("Congratulations [firstname],\n\nYou have been rewarded for:\n\n[message]");

        } else if ($type === static::TYPE_USER_RAFFLE_WON) {
            $subject = "It's time to celebrate! You won a raffle draw";
            $content = markdown_to_html("Hi [firstname],\n\nIt's your lucky day! You are the winner of the [itemname] raffle draw. "
                . "To get it, please go to \"Pending Orders\" and complete the order.");

        } else if ($type === static::TYPE_USER_SWEEPSTAKES_WON) {
            $subject = "It's time to celebrate! You won a sweepstakes draw!";
            $content = markdown_to_html("Hi [firstname],\n\nIt's your lucky day! You are the winner of the [itemname] sweepstakes draw. "
                . "To get it, please go to \"Pending Orders\" and complete the order.");

        } else {
            throw new \coding_exception('Missing content for type ' . $type);
        }

        return [$subject, $content];
    }

    /**
     * Generate the content to send.
     *
     * @param object $template The template.
     * @param object $targetuser The target user.
     * @param object|null $data The data of additional placeholders
     */
    public function generate_content($template, $targetuser, object $data = null) {
        $userplaceholders = [
            '[firstname]' => s($targetuser->firstname),
            '[lastname]' => s($targetuser->lastname),
            '[fullname]' => s(fullname($targetuser)),
        ];

        // Construct the type placeholders.
        $type = $this->get_type($template->code);
        $typeplaceholders = [];
        foreach ($type->placeholders as $placeholder) {
            if (!isset($data->{$placeholder->tag})) {
                continue;
            }
            $value = $data->{$placeholder->tag};
            $typeplaceholders['[' . $placeholder->tag . ']'] = s($value);
        }

        $placeholders = $userplaceholders + $typeplaceholders;
        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->subject);
        $content = str_replace(array_keys($placeholders), array_values($placeholders), $template->content);

        $html = format_text($content, $template->contentformat, [
            'noclean' => true,
            'filter' => false,
            'context' => context_system::instance()
        ]);

        return [$subject, $html];
    }

    /**
     * Get default templates.
     *
     * @param string $code The code.
     * @return object
     */
    public function get_default_template($type) {
        global $DB;
        return $DB->get_record_select('block_motrain_msgtpl', 'code = ? AND lang IS NULL', [$type], '*', MUST_EXIST);
    }

    /**
     * Get a particular type.
     *
     * @param sring $code Its code.
     */
    public function get_type($code) {
        $type = $this->types[$code] ?? null;
        if ($type === null) {
            throw new \coding_exception('Unknown template type ' . $code);
        }
        return $type;
    }

    /**
     * Get the type name.
     *
     * @param string $code The code.
     * @return string
     */
    public function get_type_name($code) {
        return $this->get_type($code)->name ?? $code;
    }

    /**
     * Get all types, indexed by code.
     *
     * @return object[]
     */
    public function get_types() {
        return $this->types;
    }

    /**
     * Get all templates.
     *
     * @return object[]
     */
    public function get_templates() {
        global $DB;
        return $DB->get_records('block_motrain_msgtpl', [], 'code ASC, lang ASC');
    }

    /**
     * Whether the template is a default one.
     *
     * @param object $template The record.
     * @return bool
     */
    public function is_a_default_template($template) {
        return $template->lang === null;
    }

    /**
     * Whether the template type code is valid.
     *
     * @param string $code The code.
     * @return bool
     */
    public function is_valid_template_type($code) {
        return in_array($code, array_column($this->get_types(), 'code'));
    }

    /**
     * Resolve the best template.
     *
     * @param string $code The code.
     * @param string $lang The language.
     * @return object|null The templare record or null.
     */
    public function resolve_best_template($code, $lang) {
        global $DB;

        $candidatelangs = static::get_candidate_languages_from_language($lang);

        list($insql, $inparams) = $DB->get_in_or_equal($candidatelangs, SQL_PARAMS_NAMED);
        $select = "code = :code AND (lang $insql OR lang IS NULL) AND enabled = :enabled";
        $params = array_merge([
            'code' => $code,
            'enabled' => 1
        ], $inparams);
        $records = $DB->get_records_select('block_motrain_msgtpl', $select, $params);

        $templates = array_reduce($records, function($carry, $record) {
            $carry[$record->lang ?? '_'] = $record;
            return $carry;
        }, []);

        foreach ($candidatelangs as $candidatelang) {
            if (isset($templates[$candidatelang])) {
                return $templates[$candidatelang];
            }
        }

        return $templates['_'] ?? null;
    }
    /**
     * Send notification to user.
     *
     * @param object $user The user.
     * @param object $template The template.
     * @param object|null $data Extra placeholder data indexed by key.
     * @return bool Whether successfully delivered.
     */
    public function send_notification($user, $template, $data = null) {
        $userfrom = core_user::get_noreply_user();

        [$subject, $html] = $this->generate_content($template, $user, (object) $data);

        $message = new \core\message\message();
        $message->component         = 'block_motrain';
        $message->name              = 'notification';
        $message->notification      = 1;
        $message->userto            = $user;
        $message->userfrom          = $userfrom;
        $message->subject           = $subject;
        $message->fullmessage       = html_to_text($html);
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml   = $html;

        return (bool) message_send($message);
    }

    /**
     * Send a preview to an email.
     *
     * @param object $template The template record.
     * @param string $email The email.
     * @return bool Whether successful.
     */
    public function send_preview_to_email($template, $email) {
        global $USER;

        $type = $this->get_type($template->code);
        $data = (object) array_reduce($type->placeholders, function($carry, $placeholder) {
            $carry[$placeholder->tag] = $placeholder->example;
            return $carry;
        }, []);
        [$subject, $html] = $this->generate_content($template, $USER, $data);

        $fakeuser = core_user::get_noreply_user();
        $fakeuser->firstname = '';
        $fakeuser->lastname = '';
        $fakeuser->emailstop = 0;
        $fakeuser->email = $email;
        $fakeuser->mailformat = 1;

        return email_to_user($fakeuser, core_user::get_noreply_user(), $subject, html_to_text($html), $html);
    }

    /**
     * Get the list of candidate languages for a language.
     *
     * E.g. en_us will return ['en_us', 'en'].
     *
     * @param string $lang
     * @return string[]
     */
    public static function get_candidate_languages_from_language($lang) {
        return array_reduce(explode('_', $lang), function($carry, $part) {
            $prefix = !empty($carry) ? implode('_', $carry) . '_' : '';
            return array_merge([$prefix . $part], $carry);
        }, []);
    }

}

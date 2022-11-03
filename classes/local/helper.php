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
 * Helper.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\local;

use context;

/**
 * Helper.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * External format string, but unescaped.
     *
     * See {@link self::format_string_unescaped} for reasoning.
     *
     * @param string $content The content.
     * @param context|int $contextorid The context, or its ID.
     */
    public static function external_format_string_unescaped($content, $contextorid) {
        $text = external_format_string($content, $contextorid, true, ['escape' => false]);

        // Reverts Totara 13+ forced escaping of entities.
        $trans = [
            '&#38;' => '&',
            '&#60;' => '<',
            '&#62;' => '>',
            '&#34;' => '"',
            '&#39;' => "'",
            '&#123;' => '{',
            '&#125;' => '}',
        ];
        $text = strtr($text, $trans);

        return $text;
    }

    /**
     * Format string, but unescaped.
     *
     * Why this? In some rare occasions we do not want any escaping to be done
     * on some content, but we still want filters to be applied. For instance,
     * if we provide content from an API, none of the HTML entities should
     * have been escaped. Similarly, when we prepare data to be fed to JavaScript
     * via a JSON script tag, the HTML entities do not need to be escaped if the
     * JavaScript framework does the escaping for us.
     *
     * As of today, there are two places in Motrain where this is happening:
     *
     * - On the rules screen, the list of courses is passed to a JSON script which
     *   is then loaded and rendered by a React app, which does the safeguarding.
     * - On the rules screen, the list of activities is loaded via Ajax and also
     *   must not be escaped as they are rendered by React as well.
     *
     * @param string $content The content.
     * @param context $context The context.
     */
    public static function format_string_unescaped($content, context $context) {
        $text = format_string($content, true, ['context' => $context, 'escape' => false]);

        // Reverts Totara 13+ forced escaping of entities.
        $trans = [
            '&#38;' => '&',
            '&#60;' => '<',
            '&#62;' => '>',
            '&#34;' => '"',
            '&#39;' => "'",
            '&#123;' => '{',
            '&#125;' => '}',
        ];
        $text = strtr($text, $trans);

        return $text;
    }

}

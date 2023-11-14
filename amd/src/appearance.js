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
 * Appearance.
 *
 * @copyright  2019 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/log'], function(log) {

    var wasSetup = false;

    // Should contain all the values we may be using.
    var appearance = {
        thousandssep: ',',
        pointsimageurl: M.util.image_url('coins', 'block_motrain'),
    };

    /**
     * Format coins.
     *
     * @param {Number} amount The amount.
     * @return {String}
     */
    function formatCoins(amount) {
        ensureConfigLoaded();

        var coins = amount;
        var thousandssep = appearance.thousandssep;

        // Credit: https://stackoverflow.com/a/2901298/867720
        return coins.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousandssep);
    }

    /**
     * Ensure the config is loaded.
     */
    function ensureConfigLoaded() {
        if (wasSetup) {
            return;
        }
        loadConfig();
        wasSetup = true;
    }

    /**
     * Load the config.
     */
    function loadConfig() {
        var node = document.getElementById('block_motrain-appearance-settings-data');
        try {
            var config = JSON.parse(node.textContent);
            appearance = Object.assign({}, appearance, config);
        } catch (e) {
            log.warn('Could not load appearance settings');
        }
    }

    /**
     * Setup.
     *
     * @deprecated Since Motrain v1.12.1, the config auto loads.
     */
    function setup() {
        ensureConfigLoaded();
    }

    return {
        formatCoins: formatCoins,
        getSettings: function() {
            ensureConfigLoaded();
            return appearance;
        },
        setup: setup,
    };
});

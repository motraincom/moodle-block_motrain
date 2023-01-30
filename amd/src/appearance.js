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
        thousandsep: ',',
        pointsimageurl: M.util.image_url('coins', 'block_motrain'),
    };

    /**
     * Format coins.
     *
     * @param {Number} amount The amount.
     * @return {String}
     */
    function formatCoins(amount) {
        if (!wasSetup) {
            log.warn('The appearance was not setup, make sure you used \'get_appearance_page_requirements\'.');
        }

        var coins = amount;
        var thousandsep = appearance.thousandsep;

        // Credit: https://stackoverflow.com/a/2901298/867720
        return coins.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousandsep);
    }

    /**
     * Setup.
     *
     * @param {Object} appearanceSettings The settings.
     */
    function setup(appearanceSettings) {
        appearance = Object.assign({}, appearance, appearanceSettings);
        wasSetup = true;
    }

    return {
        formatCoins: formatCoins,
        getSettings: function() {
            return appearance;
        },
        setup: setup,
    };
});

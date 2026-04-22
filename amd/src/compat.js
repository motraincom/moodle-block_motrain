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
 * Compatibility module.
 *
 * @module     block_motrain/compat
 * @copyright  2026 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/modal'], function (Modal) {
    const IS_MODAL_TYPE_DEPRECATED = 'create' in Modal;

    /**
     * Load an AMD module.
     *
     * @param {String} name
     * @returns {Promise<*>}
     */
    const getModuleAsync = (name) => {
        return new Promise((resolve, reject) => {
            require([name], resolve, reject);
        });
    };

    /**
     * Create a modal.
     *
     * Compatibility function until we drop support for Moodle <4.3.
     *
     * @param {Object} config
     * @param {Function} [ModalClass]
     * @returns {Promise<Modal>}
     */
    const createModal = (config, ModalClass = Modal) => {
        if (IS_MODAL_TYPE_DEPRECATED) {
            delete config.type;
            return ModalClass.create(config);
        }

        return Promise.all([
            getModuleAsync('core/modal_factory'),
            getModuleAsync('core/modal_registry'),
            getModuleAsync('core/modal_save_cancel'),
            getModuleAsync('core/modal_cancel'),
        ]).then(([ModalFactory, ModalRegistry, ModalSaveCancel, ModalCancel]) => {
            let typeName = config.type ?? config.template;

            // If config does not provide the type or template, guess the type and template from the class object.
            let legacyName = 'DEFAULT';
            let legacyTemplate = 'core/modal';
            if (!typeName) {
                if (ModalClass === ModalSaveCancel) {
                    legacyName = 'SAVE_CANCEL';
                    legacyTemplate = 'core/modal_save_cancel';
                } else if (ModalClass === ModalCancel) {
                    legacyName = 'CANCEL';
                    legacyTemplate = 'core/modal_cancel';
                }
            }

            typeName = typeName ?? legacyName;
            if (!ModalRegistry.get(typeName)) {
                const templateName = config.template ?? legacyTemplate;
                ModalRegistry.register(typeName, ModalClass, templateName);
            }

            return ModalFactory.create({
                ...config,
                type: typeName,
            });
        });
    };

    return {
        createModal: createModal,
    };
});

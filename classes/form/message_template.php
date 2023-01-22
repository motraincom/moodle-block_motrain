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
 * Form.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_motrain\form;
defined('MOODLE_INTERNAL') || die();

use context_system;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_template extends \moodleform {

    /**
     * Definition.
     */
    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;
        $editoroptions = ['maxfiles' => 0, 'trusttext' => true, 'context' => context_system::instance()];

        $mform->addElement('hidden', 'code');
        $mform->setConstant('code', $this->_customdata['code']);
        $mform->setType('code', PARAM_ALPHANUMEXT);

        if (!$this->is_default_template()) {
            $mform->addElement('select', 'enabled', get_string('templateenabled', 'block_motrain'), [
                0 => get_string('no', 'core'),
                1 => get_string('yes', 'core'),
            ]);
            $mform->addHelpButton('enabled', 'templateenabled', 'block_motrain');

            $strman = get_string_manager();
            $langs = $strman->get_list_of_translations();
            $mform->addElement('select', 'lang', get_string('templatelanguage', 'block_motrain'), $langs);
            $mform->setDefault('lang', $CFG->lang);
            $mform->addHelpButton('lang', 'templatelanguage', 'block_motrain');
            if (count($langs) <= 1) {
                $mform->hardFreeze('lang');
                $mform->setConstant('lang', $CFG->lang);
            }

        } else {
            $mform->addElement('static', 'langstatic', get_string('templatelanguage', 'block_motrain'),
                get_string('templatelanguageanyfallback', 'block_motrain'));
        }

        $mform->addElement('text', 'subject', get_string('templatesubject', 'block_motrain'), ['size' => 60]);
        $mform->setDefault('subject', 'Congratulations [firstname]!');
        $mform->addHelpButton('subject', 'templatesubject', 'block_motrain');
        $mform->setType('subject', PARAM_TEXT);

        $mform->addElement('editor', 'content', get_string('templatecontent', 'block_motrain'),
            null, $editoroptions);
        $mform->addHelpButton('content', 'templatecontent', 'block_motrain');

        $mform->addElement('text', 'previewemail', get_string('previewemail', 'block_motrain'));
        $mform->setDefault('previewemail', $USER->email);
        $mform->setType('previewemail', PARAM_EMAIL);
        $mform->addHelpButton('previewemail', 'previewemail', 'block_motrain');

        // Action buttons.
        $buttons = [
            $mform->createElement('submit', 'submitbutton', get_string('savechanges', 'core')),
            $mform->createElement('submit', 'previewbutton', get_string('sendpreview', 'block_motrain')),
            $mform->createElement('cancel'),
        ];
        $mform->addGroup($buttons, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Whether we can set the language.
     *
     * @return bool
     */
    protected function is_default_template() {
        return $this->_customdata['template'] && !$this->_customdata['template']->lang;
    }

    /**
     * Get data.
     *
     * @return object|null
     */
    public function get_data() {
        $data = parent::get_data();
        if ($data === null) {
            return $data;
        }

        if ($this->is_default_template()) {
            $data->lang = null;
            $data->enabled = true;
        }

        return $data;
    }

    /**
     * Validation.
     *
     * @param array $data The data.
     * @param array $files The files.
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Check uniqueness.
        $code = $this->_customdata['code'];
        $template = $this->_customdata['template'];
        $sql = '(code = :code AND lang = :lang)';
        $params = ['lang' => $data['lang'] ?? null, 'code' => $code];
        if ($template) {
            $sql .= ' AND id != :id';
            $params['id'] = $template->id;
        }
        if ($DB->record_exists_select('block_motrain_msgtpl', $sql, $params)) {
            $errors['lang'] = get_string('templateforlangalreadyexists', 'block_motrain');
        }

        if (!empty($data['previewbutton']) && empty($data['previewemail'])) {
            $errors['previewemail'] = get_string('enteremail', 'core');
        }

        return $errors;
    }

}

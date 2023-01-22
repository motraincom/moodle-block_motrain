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
 * Settings.
 *
 * @package    block_motrain
 * @copyright  2023 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_motrain\form\message_template;
use block_motrain\manager;
use core\notification;

require_once(__DIR__ . '/../../config.php');

$code = optional_param('code', null, PARAM_ALPHANUMEXT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$id = optional_param('id', null, PARAM_INT);

require_login();
$manager = manager::instance();
$manager->require_manage();

$urlparams = array_filter(['action' => $action, 'code' => $code, 'id' => $id]);
$baseurl = new moodle_url('/blocks/motrain/settings_message_templates.php');
$pageurl = new moodle_url($baseurl, $urlparams);

$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('messagetemplates', 'block_motrain'));
$PAGE->set_pagelayout('admin');

$output = $PAGE->get_renderer('block_motrain');
$messagedealer = $manager->get_message_dealer();
$messagedealer->create_missing_default_templates();

// Read the template, if any.
$template = null;
if ($id) {
    $template = $DB->get_record('block_motrain_msgtpl', ['id' => $id], '*', MUST_EXIST);
}

// Process deletion.
if ($action === 'delete') {
    if (!$template || !$messagedealer->can_delete_template($template)) {
        redirect($baseurl);
    }
    require_sesskey();
    $DB->delete_records('block_motrain_msgtpl', ['id' => $template->id]);
    redirect($baseurl, get_string('templatedeleted', 'block_motrain'));
}

// If we have a template, we get the code from it.
if ($template) {
    $code = $template->code;
}

// If we got a code, validate it.
if ($code && !$messagedealer->is_valid_template_type($code)) {
    throw new \moodle_exception('unknowntemplatecode', 'block_motrain', '', $code);
}

// We need a code when we're editing.
if ($action === 'edit' && !$code) {
    redirect($baseurl);
}

// Prepare the form.
$form = new message_template($pageurl->out(false), ['code' => $code, 'template' => $template]);
if ($action === 'edit') {
    $templatesource = $template ?? $messagedealer->get_default_template($code);
    $form->set_data([
        'lang' => $templatesource->lang,
        'subject' => $templatesource->subject,
        'content' => ['text' => $templatesource->content, 'format' => $templatesource->contentformat],
        'enabled' => $templatesource->enabled,
    ]);
    unset($templatesource);
    unset($formdefaultdata);
}

// Process the form.
if ($data = $form->get_data()) {
    if ($template) {
        $record = (object) (array) $template;
        $record->lang = $data->lang;
        $record->enabled = $data->enabled;
        $record->subject = $data->subject;
        $record->content = $data->content['text'];
        $record->contentformat = $data->content['format'];

    } else {
        $record = (object) [
            'id' => 0,
            'code' => $data->code,
            'lang' => $data->lang,
            'subject' => $data->subject,
            'content' => $data->content['text'],
            'contentformat' => $data->content['format'],
            'enabled' => $data->enabled,
        ];
    }

    if (!empty($data->previewbutton)) {
        if ($messagedealer->send_preview_to_email($record, $data->previewemail)) {
            notification::add(get_string('previewsent', 'block_motrain'), notification::INFO);
        } else {
            notification::add(get_string('previewnotsent', 'block_motrain'), notification::ERROR);
        }
        // No redirect, we redisplay the form.

    } else {
        if ($template) {
            $DB->update_record('block_motrain_msgtpl', $record);
        } else {
            $DB->insert_record('block_motrain_msgtpl', $record);
        }
        redirect($baseurl, get_string('templatesaved', 'block_motrain'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

} else if ($form->is_cancelled()) {
    redirect($baseurl);
}


// Display the page.
echo $output->header();
echo $output->heading(get_string('pluginname', 'block_motrain'));
echo $output->navigation_for_managers($manager, 'messagetemplates');

if (!$manager->is_enabled()) {
    echo $output->notification(get_string('pluginnotenabledseesettings', 'block_motrain'));
} else if (!$manager->is_sending_local_notifications_enabled()) {
    echo $output->notification(get_string('sendlocalnotificationsnotenabledseesettings', 'block_motrain'),
        \core\output\notification::NOTIFY_WARNING);
}

// TODO Display something about webhooks.
if ($action === 'edit') {

    echo $output->heading(get_string('templatex', 'block_motrain', $messagedealer->get_type_name($code)));
    $form->display();

} else {

    echo html_writer::tag('p', get_string('messagetemplatesintro', 'block_motrain'));

    // Load the languages.
    $strman = get_string_manager();
    $langs = $strman->get_list_of_translations();

    // Get and sort templates by code. We place the default template last.
    $alltemplates = array_reduce($messagedealer->get_templates(), function($carry, $record) {
        $carry[$record->code] = $carry[$record->code] ?? [];
        $carry[$record->code][] = $record;
        return $carry;
    }, []);
    foreach ($alltemplates as $code => $array) {
        usort($array, function($a, $b) {
            if ($a->lang === null) {
                return 1;
            } else if ($b->lang === null) {
                return -1;
            }
            return $a->lang <=> $b->lang;
        });
    }

    $sortedtypes = $messagedealer->get_types();
    core_collator::asort_objects_by_property($sortedtypes, 'name', core_collator::SORT_NATURAL);

    foreach ($sortedtypes as $typedata) {
        $code = $typedata->code;
        $templates = $alltemplates[$code];

        echo $output->heading($typedata->name, 3);

        $addurl = new moodle_url($baseurl, ['action' => 'edit', 'code' => $code]);

        $table = new html_table();
        $table->head = [
            get_string('language', 'core'),
            get_string('templateenabled', 'block_motrain'),
            $output->action_link($addurl, get_string('add', 'core'), null, null, new pix_icon('t/add', '')),
        ];

        foreach ($templates as $template) {
            $editurl = new moodle_url($baseurl, ['action' => 'edit', 'code' => $template->code, 'id' => $template->id]);
            $deleteurl = new moodle_url($baseurl, ['action' => 'delete', 'id' => $template->id, 'sesskey' => sesskey()]);
            $actions = [
                new action_link($editurl, '', null, null, new pix_icon('t/edit', get_string('edit', 'core')))
            ];

            if (!$messagedealer->is_a_default_template($template)) {
                $deleteaction = new action_link($deleteurl, '', new confirm_action(get_string('areyousure', 'core')),
                    null, new pix_icon('t/delete', get_string('delete', 'core')));
                $actions[] = $deleteaction;
            }

            $lang = get_string('templatelanguageanyfallback', 'block_motrain');
            if ($template->lang) {
                $lang = $langs[$template->lang] ?? $template->lang;
            }

            $table->data[] = [
                html_writer::link($editurl, $lang),
                $template->enabled ?? false ? get_string('yes', 'core') : get_string('no', 'core'),
                implode('', array_map(function($item) use ($output) {
                    return $output->render($item);
                }, $actions))
            ];
        }

        echo html_writer::table($table);
    }

}

echo $output->footer();

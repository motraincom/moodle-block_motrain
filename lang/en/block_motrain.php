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
 * Language.
 *
 * @package    block_motrain
 * @copyright  2022 Mootivation Technologies Corp.
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['editassociation'] = 'Edit association';
$string['createassociation'] = 'Create association';
$string['addactivityellipsis'] = 'Add activity...';
$string['addcourseellipsis'] = 'Add course...';
$string['autopush'] = 'Automatically push users';
$string['autopush_help'] = 'When enabled, users will be automatically added as players in the Motrain dashbard, even before they start earning coins. This process is done during cron, it may take several minutes for players to appear on the dashboard.';
$string['adminscanearn'] = 'Admins can earn coins';
$string['adminscanearn_desc'] = 'When enabled, this allows administrators to earn coins.';
$string['apihost'] = 'API endpoint';
$string['apihost_desc'] = 'The URL at which the API is located.';
$string['apikey'] = 'API key';
$string['apikey_desc'] = 'The API key to authenticate with the API.';
$string['accountid'] = 'Account ID';
$string['accountid_desc'] = 'The ID of the account.';
$string['cachedef_comprules'] = 'Completion rules';
$string['coins'] = 'Coins';
$string['coursecompletion'] = 'Course completion';
$string['completingn'] = 'Completing {$a}';
$string['completingacourse'] = 'Completing a course';
$string['configfootercontent'] = 'Footer content';
$string['configfootercontent_help'] = 'The content to display at the bottom of the block.';
$string['configtitle'] = 'Title';
$string['cohort'] = 'Cohort';
$string['cohort_help'] = 'The cohort to associate with the team, or "All users" when no specific cohort is required.';
$string['coinrules'] = 'Coin rules';
$string['defaultparens'] = '(default)';
$string['defaulttitle'] = 'Motrain';
$string['globalsettings'] = 'Global settings';
$string['pluginname'] = 'Motrain';
$string['motrain:addinstance'] = 'Add a new Motrain block';
$string['motrain:myaddinstance'] = 'Add the Motrain block on the dashboard';
$string['motrain:view'] = 'View the content of the Motrain block';
$string['nocohortallusers'] = 'All users';
$string['nooptions'] = 'No options';
$string['saving'] = "Saving...";
$string['saverules'] = "Save rules";
$string['teamassociationcreated'] = 'Team association created.';
$string['teamassociations'] = 'Team associations';
$string['teams'] = 'Teams';
$string['team'] = 'Team';
$string['team_help'] = 'The Motrain team that the cohort will be associated with.';
$string['noteamsyetcreatefirst'] = 'No team association exist yet, please start by creating one.';
$string['reallydeleteassociation'] = 'Do you really want to delete the association?';
$string['usecohorts'] = 'Use cohorts';
$string['usecohorts_help'] = 'When enabled, users can be organised in different teams using cohorts.';
$string['userecommended'] = 'Use recommended';
$string['unknowncoursen'] = 'Unknown course {$a}';
$string['unknownactivityn'] = 'Unknown activity {$a}';
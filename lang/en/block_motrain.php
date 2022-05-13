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

$string['eventcoinsearned'] = 'Coins earned';
$string['infopagetitle'] = 'Info';
$string['store'] = 'Store';
$string['purchases'] = 'My purchases';
$string['leaderboard'] = 'Leaderboard';
$string['dashboard'] = 'Dashboard';
$string['manageaddons'] = 'Manage add-ons';
$string['editassociation'] = 'Edit association';
$string['invalidcoinamount'] = 'Invalid amount of coins';
$string['playeridnotfound'] = 'The player associated with the current user could not be found, please contact the adminstrator.';
$string['userteamnotfound'] = 'The team of the current user could not be found, please contact the adminstrator.';
$string['usernotplayer'] = 'The user does not meet the criteria to be a player.';
$string['createassociation'] = 'Create association';
$string['addactivityellipsis'] = 'Add activity...';
$string['addcourseellipsis'] = 'Add course...';
$string['addons'] = 'Addons';
$string['autopush'] = 'Automatically push users';
$string['autopush_help'] = 'When enabled, users will be automatically added as players in the Motrain dashbard, even before they start earning coins. This process is done during cron, it may take several minutes for players to appear on the dashboard. Note that this does not apply to existing cohort-team associations, only new members and new associations.';
$string['adminscanearn'] = 'Admins can earn coins';
$string['adminscanearn_desc'] = 'When enabled, this allows administrators to earn coins.';
$string['apihost'] = 'API endpoint';
$string['apihost_desc'] = 'The URL at which the API is located.';
$string['apikey'] = 'API key';
$string['apikey_desc'] = 'The API key to authenticate with the API.';
$string['accountid'] = 'Account ID';
$string['accountid_desc'] = 'The ID of the account.';
$string['cachedef_coins'] = 'User coins';
$string['cachedef_comprules'] = 'Completion rules';
$string['cachedef_metadata'] = 'Metadata';
$string['coins'] = 'Coins';
$string['coinsimage'] = 'Coins image';
$string['coinsimage_help'] = 'Use setting to use an alternate image representing the coins displayed in the block.';
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
$string['disabled'] = 'Disabled';
$string['enableaddon'] = 'Enable add-on';
$string['enableaddon_help'] = 'An add-on must be enabled to work.';
$string['enabled'] = 'Enabled';
$string['isenabled'] = 'Plugin enabled';
$string['isenabled_desc'] = 'To enable the plugin, please fill in the settings below with the correct information. For as long as the API details are correct, the plugin will enable itself.';
$string['globalsettings'] = 'Global settings';
$string['playersmapping'] = 'Players mapping';
$string['pluginname'] = 'Motrain';
$string['purgecache'] = 'Purge cache';
$string['addon'] = 'Add-on';
$string['metadatacache'] = 'Metadata cache';
$string['metadatacache_help'] = 'The plugin keeps a cache of some metadata from the API to improve performance. It stores information such as which leaderboards are enabled, etc. After making some changes on the Motrain dashboard, you may need to manually purge this cache. You can do so by clicking the link above.';
$string['motrainaddons'] = 'Motrain add-ons';
$string['motrain:accessdashboard'] = 'Access the Motrain dashboard';
$string['motrain:addinstance'] = 'Add a new Motrain block';
$string['motrain:awardcoins'] = 'Award coins to other users';
$string['motrain:earncoins'] = 'Earn coins';
$string['motrain:myaddinstance'] = 'Add the Motrain block on the dashboard';
$string['motrain:view'] = 'View the content of the Motrain block';
$string['noaddoninstalled'] = 'No add-ons have been installed yet.';
$string['nocohortallusers'] = 'All users';
$string['notenabled'] = 'The plugin is not enabled, please contact your administrator.';
$string['nooptions'] = 'No options';
$string['playerid'] = 'Player ID';
$string['playermappingintro'] = 'A player mapping is the association between a local user and a player on the Motrain dashboard. You can find the list of known mappings below. Mappings with an error will not be re-attempted, please fix the issue and reset the mapping.';
$string['pleasewait'] = 'Please wait...';
$string['pluginnotenabledseesettings'] = 'The plugin is not enabled, please see its settings.';
$string['saving'] = "Saving...";
$string['saverules'] = "Save rules";
$string['taskpushusers'] = 'Push users to dashboard';
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
$string['addonstate'] = 'State';
$string['addonstate_desc'] = 'The state of the add-on. Some add-ons require some settings to be set before they can be enabled, or enable themselves.';
$string['setup'] = 'Set-up';
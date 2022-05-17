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

$string['accountid_desc'] = 'The ID of the account.';
$string['accountid'] = 'Account ID';
$string['addactivityellipsis'] = 'Add activity...';
$string['addcourseellipsis'] = 'Add course...';
$string['addon'] = 'Add-on';
$string['addons'] = 'Addons';
$string['addonstate_desc'] = 'The state of the add-on. Some add-ons require some settings to be set before they can be enabled, or enable themselves.';
$string['addonstate'] = 'State';
$string['adminscanearn_desc'] = 'When enabled, this allows administrators to earn coins.';
$string['adminscanearn'] = 'Admins can earn coins';
$string['apihost_desc'] = 'The URL at which the API is located.';
$string['apihost'] = 'API endpoint';
$string['apikey_desc'] = 'The API key to authenticate with the API.';
$string['apikey'] = 'API key';
$string['autopush_help'] = 'When enabled, users will be automatically added as players in the Motrain dashbard, even before they start earning coins. This process is done during cron, it may take several minutes for players to appear on the dashboard. Note that this does not apply to existing cohort-team associations, only new members and new associations.';
$string['autopush'] = 'Automatically push users';
$string['cachedef_coins'] = 'User coins';
$string['cachedef_comprules'] = 'Completion rules';
$string['cachedef_metadata'] = 'Metadata';
$string['cohort_help'] = 'The cohort to associate with the team, or "All users" when no specific cohort is required.';
$string['cohort'] = 'Cohort';
$string['coinrules'] = 'Coin rules';
$string['coins'] = 'Coins';
$string['coinsimage_help'] = 'Use setting to use an alternate image representing the coins displayed in the block.';
$string['coinsimage'] = 'Coins image';
$string['completingacourse'] = 'Completing a course';
$string['completingn'] = 'Completing {$a}';
$string['configfootercontent_help'] = 'The content to display at the bottom of the block.';
$string['configfootercontent'] = 'Footer content';
$string['configtitle'] = 'Title';
$string['coursecompletion'] = 'Course completion';
$string['createassociation'] = 'Create association';
$string['dashboard'] = 'Dashboard';
$string['defaultparens'] = '(default)';
$string['defaulttitle'] = 'Motrain';
$string['disabled'] = 'Disabled';
$string['editassociation'] = 'Edit association';
$string['enableaddon_help'] = 'An add-on must be enabled to work.';
$string['enableaddon'] = 'Enable add-on';
$string['enabled'] = 'Enabled';
$string['eventcoinsearned'] = 'Coins earned';
$string['globalsettings'] = 'Global settings';
$string['infopagetitle'] = 'Info';
$string['invalidcoinamount'] = 'Invalid amount of coins';
$string['isenabled_desc'] = 'To enable the plugin, please fill in the settings below with the correct information. For as long as the API details are correct, the plugin will enable itself.';
$string['isenabled'] = 'Plugin enabled';
$string['leaderboard'] = 'Leaderboard';
$string['manageaddons'] = 'Manage add-ons';
$string['metadatacache_help'] = 'The plugin keeps a cache of some metadata from the API to improve performance. It stores information such as which leaderboards are enabled, etc. After making some changes on the Motrain dashboard, you may need to manually purge this cache. You can do so by clicking the link above.';
$string['metadatacache'] = 'Metadata cache';
$string['motrain:accessdashboard'] = 'Access the Motrain dashboard';
$string['motrain:addinstance'] = 'Add a new Motrain block';
$string['motrain:awardcoins'] = 'Award coins to other users';
$string['motrain:earncoins'] = 'Earn coins';
$string['motrain:myaddinstance'] = 'Add the Motrain block on the dashboard';
$string['motrain:view'] = 'View the content of the Motrain block';
$string['motrainaddons'] = 'Motrain add-ons';
$string['noaddoninstalled'] = 'No add-ons have been installed yet.';
$string['nocohortallusers'] = 'All users';
$string['nooptions'] = 'No options';
$string['noteamsyetcreatefirst'] = 'No team association exist yet, please start by creating one.';
$string['notenabled'] = 'The plugin is not enabled, please contact your administrator.';
$string['playerid'] = 'Player ID';
$string['playeridnotfound'] = 'The player associated with the current user could not be found, please contact the adminstrator.';
$string['playermappingintro'] = 'A player mapping is the association between a local user and a player on the Motrain dashboard. You can find the list of known mappings below. Mappings with an error will not be re-attempted, please fix the issue and reset the mapping.';
$string['playersmapping'] = 'Players mapping';
$string['pleasewait'] = 'Please wait...';
$string['pluginname'] = 'Motrain';
$string['pluginnotenabledseesettings'] = 'The plugin is not enabled, please see its settings.';
$string['privacy:metadata:coinsgained:coins'] = 'The number of coins to award';
$string['privacy:metadata:coinsgained:reason'] = 'The reason for the award';
$string['privacy:metadata:coinsgained'] = 'Information sent when awarding coins.';
$string['privacy:metadata:log:userid'] = 'The user ID';
$string['privacy:metadata:log:contextid'] = 'The context ID';
$string['privacy:metadata:log:coins'] = 'The number of coins';
$string['privacy:metadata:log:actionname'] = 'The action name';
$string['privacy:metadata:log:actionhash'] = 'The hash of the action';
$string['privacy:metadata:log:timecreated'] = 'The time at which the log was created';
$string['privacy:metadata:log:timebroadcasted'] = 'The time at which the log was broadcasted';
$string['privacy:metadata:log:broadcasterror'] = 'The error for failed broadcasts';
$string['privacy:metadata:log'] = 'Coins log';
$string['privacy:metadata:playermap:userid'] = 'The user ID';
$string['privacy:metadata:playermap:accountid'] = 'The Motrain account ID';
$string['privacy:metadata:playermap:playerid'] = 'The Motrain player ID';
$string['privacy:metadata:playermap:blocked'] = 'Whether this mapping is blocked';
$string['privacy:metadata:playermap:blockedreason'] = 'The reason for it to be blocked';
$string['privacy:metadata:playermap'] = 'Mapping of local users and Motrain players';
$string['privacy:metadata:remoteplayer:firstname'] = 'First name';
$string['privacy:metadata:remoteplayer:lastname'] = 'Last name';
$string['privacy:metadata:remoteplayer:email'] = 'Email';
$string['privacy:metadata:remoteplayer'] = 'Informatin exchanged when creating or identifying a Motrain player.';
$string['privacy:metadata:userspush:userid'] = 'The user ID';
$string['privacy:metadata:userspush'] = 'A queue of users to be pushed to the Motrain dashboard.';
$string['privacy:path:logs'] = 'Logs';
$string['privacy:path:mappings'] = 'Mappings';
$string['purchases'] = 'My purchases';
$string['purgecache'] = 'Purge cache';
$string['reallydeleteassociation'] = 'Do you really want to delete the association?';
$string['saverules'] = "Save rules";
$string['saving'] = "Saving...";
$string['setup'] = 'Set-up';
$string['store'] = 'Store';
$string['taskpushusers'] = 'Push users to dashboard';
$string['team_help'] = 'The Motrain team that the cohort will be associated with.';
$string['team'] = 'Team';
$string['teamassociationcreated'] = 'Team association created.';
$string['teamassociations'] = 'Team associations';
$string['teams'] = 'Teams';
$string['unknownactivityn'] = 'Unknown activity {$a}';
$string['unknowncoursen'] = 'Unknown course {$a}';
$string['usecohorts_help'] = 'When enabled, users can be organised in different teams using cohorts. Note that when cohorts are not used all Moodle users will be considered as potential players.';
$string['usecohorts'] = 'Use cohorts';
$string['userecommended'] = 'Use recommended';
$string['usernotplayer'] = 'The user does not meet the criteria to be a player.';
$string['userteamnotfound'] = 'The team of the current user could not be found, please contact the adminstrator.';
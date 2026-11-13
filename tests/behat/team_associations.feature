@block @block_motrain
Feature: Associate learners with teams
  In order to participate in Incentli
  As an administrator
  I associate learners with a team

  Background:
    Given the following config values are set as admin:
      | enablemyhome | 1 | |
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | learner  | Learning  | Learner  | learner@example.com |
      | other    | Other     | Learner  | other@example.com   |
    And the following "blocks" exist:
      | blockname | contextlevel | reference | pagetypepattern | defaultregion |
      | motrain   | System       | 1         | site-index      | side-pre      |

  Scenario: Create a global team association
    Given the following config values are set as admin:
      | usecohorts | 0 | block_motrain |
    And I log in as "admin"
    And I navigate to "Plugins > Blocks > Incentli > Team associations" in site administration
    When I press "Create association"
    And I set the field "Team" to "Team one"
    And I press "Save changes"
    Then I should see "Team association created."
    And I should see "Team one" in the "All users" "table_row"
    And I reload the page
    And I should see "Team one" in the "All users" "table_row"
    And I log out
    And I log in as "learner"
    And I am on site homepage
    And I should see "0" in the "Incentli" "block"

  Scenario: Only members of an associated cohort can participate
    Given the following config values are set as admin:
      | usecohorts | 1 | block_motrain |
    And the following "cohorts" exist:
      | name     | idnumber |
      | Learners | learners |
    And the following "cohort members" exist:
      | user    | cohort   |
      | learner | learners |
    And the following "block_motrain > team mappings" exist:
      | cohort   | team_id  |
      | learners | team-one |
    When I log in as "learner"
    And I am on site homepage
    Then I should see "0" in the "Incentli" "block"
    And I log out
    And I log in as "other"
    And I am on site homepage
    And "Incentli" "block" should not exist

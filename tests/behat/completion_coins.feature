@block @block_motrain
Feature: Earn coins for completion
  In order to reward learning
  As a learner
  I receive coins according to the completion rules

  Background:
    Given the following config values are set as admin:
      | enablecompletion | 1 |               |
      | usecohorts       | 0 | block_motrain |
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | learner  | Learning  | Learner  | learner@example.com |
    And the following "courses" exist:
      | fullname       | shortname | enablecompletion |
      | Learning coins | C1        | 1                |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | learner | C1     | student |
    And the following "activities" exist:
      | activity | course | name           | idnumber | completion | completionview |
      | page     | C1     | Read the guide | guide    | 2          | 1              |
      | page     | C1     | Extra reading  | extra    | 2          | 1              |
    And the following "blocks" exist:
      | blockname | contextlevel | reference | pagetypepattern | defaultregion |
      | motrain   | Course       | C1        | course-view-*   | side-pre      |
    And the following "block_motrain > team mappings" exist:
      | team_id  |
      | team-one |

  Scenario: A global activity rule awards coins once
    Given the following "block_motrain > completion rules" exist:
      | modname | coins |
      | page    | 10    |
    And I log in as "learner"
    And I am on "Learning coins" course homepage
    When I follow "Read the guide"
    And I am on "Learning coins" course homepage
    Then I should see "10" in the "Incentli" "block"
    And I follow "Read the guide"
    And I am on "Learning coins" course homepage
    And I should see "10" in the "Incentli" "block"
    And I should not see "20" in the "Incentli" "block"

  Scenario: An activity rule overrides the global award
    Given the following "block_motrain > completion rules" exist:
      | modname | coins |
      | page    | 10    |
    And the following "block_motrain > completion rules" exist:
      | activity | coins |
      | guide    | 25    |
    And I log in as "learner"
    And I am on "Learning coins" course homepage
    When I follow "Read the guide"
    And I am on "Learning coins" course homepage
    Then I should see "25" in the "Incentli" "block"
    And I follow "Extra reading"
    And I am on "Learning coins" course homepage
    And I should see "35" in the "Incentli" "block"

  Scenario: An explicit zero prevents the global award
    Given the following "block_motrain > completion rules" exist:
      | modname | coins |
      | page    | 10    |
    And the following "block_motrain > completion rules" exist:
      | activity | coins |
      | guide    | 0     |
    And I log in as "learner"
    And I am on "Learning coins" course homepage
    When I follow "Read the guide"
    And I am on "Learning coins" course homepage
    Then I should see "0" in the "Incentli" "block"
    And I should not see "10" in the "Incentli" "block"

  Scenario: Course completion uses the global award unless overridden
    Given the following "courses" exist:
      | fullname         | shortname | enablecompletion |
      | Further learning | C2        | 1                |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | learner | C2     | student |
    And the following "activities" exist:
      | activity | course | name              | completion | completionview |
      | page     | C2     | Read the handbook | 2          | 1              |
    And the following "blocks" exist:
      | blockname | contextlevel | reference | pagetypepattern | defaultregion |
      | motrain   | Course       | C2        | course-view-*   | side-pre      |
    And the following "block_motrain > completion rules" exist:
      | coins |
      | 50    |
    And the following "block_motrain > completion rules" exist:
      | course | coins |
      | C1     | 100   |
    And I log in as "admin"
    And I am on "Learning coins" course homepage
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Page - Read the guide" to "1"
    And I press "Save changes"
    And I am on "Further learning" course homepage
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Page - Read the handbook" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "learner"
    And I am on "Further learning" course homepage
    When I follow "Read the handbook"
    # Older Moodle versions only aggregate completion records from previous seconds.
    And I wait "1" seconds
    And I run the scheduled task "\core\task\completion_regular_task"
    And I am on "Further learning" course homepage
    Then I should see "50" in the "Incentli" "block"
    And I am on "Learning coins" course homepage
    And I follow "Read the guide"
    And I wait "1" seconds
    And I run the scheduled task "\core\task\completion_regular_task"
    And I am on "Learning coins" course homepage
    And I should see "150" in the "Incentli" "block"

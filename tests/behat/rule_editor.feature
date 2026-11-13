@block @block_motrain @javascript
Feature: Configure coin rules
  In order to choose how learners earn coins
  As an administrator
  I can save global and course-specific completion rules

  Background:
    Given the following "courses" exist:
      | fullname       | shortname | enablecompletion |
      | Learning coins | C1        | 1                |
    And the following "activities" exist:
      | activity | course | name             | completion |
      | page     | C1     | Read the guide   | 1          |
    And I log in as "admin"
    And I navigate to "Plugins > Blocks > Incentli > Coin rules" in site administration

  Scenario: Save custom global rules
    Given I click on "Global settings" "button"
    When I set the field "Use recommended" to "0"
    And I set the following fields to these values:
      | Completing a course | 100 |
      | Completing Page     | 10  |
    And I press "Save rules"
    And I reload the page
    And I click on "Global settings" "button"
    Then the following fields match these values:
      | Use recommended | 0   |
      | Completing a course    | 100 |
      | Completing Page        | 10  |

  Scenario: Add and edit course and activity rules
    When I click on "Add course..." "text"
    And I click on "Learning coins" "text" in the ".block_motrain-react-select__menu" "css_element"
    And I set the field "Course completion" to "100"
    And I click on "Add activity..." "text"
    And I click on "Read the guide (page)" "text" in the ".block_motrain-react-select__menu" "css_element"
    And I set the field "Read the guide (page) completion" to "20"
    And I press "Save rules"
    And I reload the page
    And I click on "Learning coins" "button"
    Then the following fields match these values:
      | Course completion                | 100 |
      | Read the guide (page) completion  | 20  |
    And I set the field "Read the guide (page) completion" to "0"
    And I press "Save rules"
    And I reload the page
    And I click on "Learning coins" "button"
    And the field "Read the guide (page) completion" matches value "0"

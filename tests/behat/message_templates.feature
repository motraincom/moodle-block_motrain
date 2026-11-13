@block @block_motrain
Feature: Configure message templates
  In order to personalise notifications
  As an administrator
  I can add and edit message templates

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Blocks > Incentli > Message templates" in site administration

  Scenario: Edit the fallback message
    When I click on "All languages (fallback)" "link" in the "Player manually awarded" message template section
    And I set the following fields to these values:
      | Subject | Well done [firstname]                |
      | Content | <p>You earned coins: [message]</p>    |
    And I press "Save changes"
    Then I should see "The template was saved"
    And I click on "All languages (fallback)" "link" in the "Player manually awarded" message template section
    And the following fields match these values:
      | Subject | Well done [firstname]                |
      | Content | <p>You earned coins: [message]</p>    |

  Scenario: Add a language-specific message and reject a duplicate
    When I click on "Add" "link" in the "Player manually awarded" message template section
    And I set the following fields to these values:
      | Enabled | Yes                           |
      | Subject | Thanks [firstname]            |
      | Content | <p>Thanks for [message]</p>    |
    And I press "Save changes"
    Then I should see "The template was saved"
    And I click on "English" "link" in the "Player manually awarded" message template section
    And the following fields match these values:
      | Enabled | Yes                           |
      | Subject | Thanks [firstname]            |
      | Content | <p>Thanks for [message]</p>    |
    And I set the field "Enabled" to "No"
    And I press "Save changes"
    And I should see "No" in the "//table[preceding::h3[1][normalize-space(.)='Player manually awarded']]//tr[td/a[contains(., 'English')]]" "xpath_element"
    And I click on "Add" "link" in the "Player manually awarded" message template section
    And I press "Save changes"
    And I should see "A template for this language already exists."

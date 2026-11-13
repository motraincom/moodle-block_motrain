@block @block_motrain
Feature: Incentli setup status
  In order to configure the integration
  As an administrator
  I can see whether Incentli is enabled

  Scenario: Configured account is enabled
    Given I log in as "admin"
    When I navigate to "Plugins > Blocks > Incentli > Settings" in site administration
    Then I should see "Yes" in the "admin-isenabled" "region"
    And the field "Account ID" matches value "account-one"
    And I follow "Team associations"
    And I should see "No team association exist yet, please start by creating one."

  Scenario: Enable Incentli through the settings form
    Given the following config values are set as admin:
      | accountid |   | block_motrain |
      | apikey    |   | block_motrain |
      | apihost   |   | block_motrain |
      | isenabled | 0 | block_motrain |
    And I log in as "admin"
    And I navigate to "Plugins > Blocks > Incentli > Settings" in site administration
    And I should see "No" in the "admin-isenabled" "region"
    And the field "Account ID" matches value ""
    When I set the Incentli API endpoint to the test server
    And I set the following fields to these values:
      | Account ID | account-one |
      | API key    | test-key    |
    And I press "Save changes"
    Then I should see "Yes" in the "admin-isenabled" "region"
    And I reload the page
    And I should see "Yes" in the "admin-isenabled" "region"
    And the field "Account ID" matches value "account-one"
    And I follow "Team associations"
    And I should see "No team association exist yet, please start by creating one."

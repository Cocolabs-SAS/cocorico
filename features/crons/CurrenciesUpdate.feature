@command

Feature: Currencies rate update command
  As a system user I want to update currencies rates so that currencies can be converted

  Scenario: I can update DB currencies rates from BCE feed and generate JSON file containing new updated rates
    When I run "cocorico:currency:update" command
    Then I should see "Currencies updated" on console messages

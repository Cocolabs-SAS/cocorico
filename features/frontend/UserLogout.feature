@default @javascript

Feature: User logout
  As a connected user I want to logout so that nobody can access to my secured contents

  Scenario: I can log out
    Given I am on the home page
    And I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Logout" of "offerer"
    Then I should be on the "home" page
    And I should see "Login"
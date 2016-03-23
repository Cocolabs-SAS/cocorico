@default @javascript

Feature: User login
  As an anonymous user I want to login so that i can access to secured contents

  Scenario: I can log in with correct username and password and enabled account
    Given I am on the home page
    And I follow "Login"
    When I fill in the following:
      | Email    | offerer@cocorico.rocks |
      | Password | 12345678               |
    And I press "Login"
    Then I should be on the "home" page
    When I click on dashboard menu of "offerer"
    Then I should see "Logout"

  Scenario: I can not log in with wrong username and password
    Given I am on the home page
    And I follow "Login"
    When I fill in the following:
      | Email    | wrong@cocorico.rocks |
      | Password | wrong                |
    And I press "Login"
    Then I should be on the "login" page
    And I should see "Bad credentials"

  Scenario: I can not log in without username and password
    Given I am on the home page
    And I follow "Login"
    When I press "Login"
    Then I should be on the "login" page
    And I should see "This value is required."

  Scenario: I can not log in with disabled user
    Given I am on the home page
    And I follow "Login"
    When I fill in the following:
      | Email    | disableuser@cocorico.rocks |
      | Password | 12345678                   |
    And I press "Login"
    Then I should be on the "login" page
    And I should see "ERROR! User account is disabled."
@default @javascript

Feature: User password resetting
  As a registered user i want to be able to request a new password so that i can login again if i forget it

  Scenario: I can request a new password and receive a resetting email with a link to change it
    Given I am on the home page
    And I am not logged in
    And I follow "Login"
    And I follow "Forgot your password"
    When I fill in the following:
      | Email | offerer@cocorico.rocks |
    And I press "Reset your password"
    Then I should be on the "resetting check email" page
    And I should see "It contains a link you must click to reset your password."
    And I should receive the "forgot_password_user" mail on "offerer@cocorico.rocks"
    When I wait 2000 ms
    And I follow the resetting reset link in the "forgot_password_user" mail send to "offerer@cocorico.rocks"
    Then I should be on the resetting password page with token of "offerer@cocorico.rocks" user
    When I fill in the following:
      | New password | mynewpassword |
      | Verification | mynewpassword |
    And I press "Change password"
    Then I should see "The password has been reset successfully"
    When I follow "My Dashboard"
    Then I should see "Logout"

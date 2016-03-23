@default @javascript

Feature: User registration
  As a new user i want to register so that i can be logged

  Scenario: I can register if all required fields (First name, Last name, Email, Username, Password) are filled. After successful registration i receive a Welcome email and i'm automatically logged.
    Given I am on the home page
    And I follow "Register"
    When I fill in the following:
      | First name   | Jean                |
      | Last name    | Lelievre            |
      | Email        | jean@cocorico.rocks |
      | Password     | 12345678            |
      | Verification | 12345678            |
    And I press "Register"
    Then I should see "Your account has been created successfully"
    And I should receive the "registration" mail on "jean@cocorico.rocks"


  Scenario: I can not register with different passwords
    Given I am on the home page
    And I follow "Register"
    When I fill in the following:
      | First name   | TestFirstName       |
      | Last name    | TestrName           |
      | Email        | test@cocorico.rocks |
      | Password     | testtest            |
      | Verification | testtes             |
    And I press "Register"
#    And I wait 1000 ms
    Then I should be on the "register" page
    And I should see "The entered passwords don't match"

  Scenario: I can not register if all required fields are not filled
    Given I am on the home page
    And I follow "Register"
    When I fill in the following:
      | First name   |  |
      | Last name    |  |
      | Email        |  |
      | Password     |  |
      | Verification |  |
    And I press "Register"
#    And I wait 1000 ms
    Then I should be on the "register" page
    And I should see "Please enter an email"
    And I should see "Please enter your first name"
    And I should see "Please enter your last name"
    And I should see "Password required"

  Scenario: I can not register with an invalid email
    Given I am on the home page
    And I follow "Register"
    When I fill in the following:
      | First name   | Jean       |
      | Last name    | Lelievre   |
      | Email        | wrongemail |
      | Password     | 12345678   |
      | Verification | 12345678   |
    And I press "Register"
#    And I wait 1000 ms
#    Then I should be on the register page
    And I should see "This value should be a valid email."


  Scenario: I can not register with an already existing email
    Given I am on the home page
    And I follow "Register"
#    And I wait 5000 ms
    When I fill in the following:
      | First name   | AskerFirstName       |
      | Last name    | AskerName            |
      | Email        | asker@cocorico.rocks |
      | Password     | 12456789             |
      | Verification | 12456789             |
    And I press "Register"
#    And I wait 1000 ms
    Then I should be on the "register" page
    And I should see "This email is already used"
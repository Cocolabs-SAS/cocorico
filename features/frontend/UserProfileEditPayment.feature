@default @javascript

Feature: User profile payment edit
  As a user i want to edit my payment info

  Scenario: Check required fields
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I follow "Payment information"
    And I press "Update"
    #And I wait 500 ms
    Then I should be on the "user dashboard profile edit payment" page
    And I should see "An error has occurred." in the "div.flashes div.alert" element

  Scenario: As a user i want to edit my payment information
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I follow "Payment information"
    And I fill in the following:
      | user_lastName         | MyLastName                        |
      | user_firstName        | MyFirstName                       |
      | user_profession       | MyProfession                      |
      | user_annualIncome     | 1000                              |
      | user_bankOwnerName    | My Bank Owner Name                |
      | user_bankOwnerAddress | My Bank Owner Address             |
      | user_iban             | FR76 1790 6000 3200 0833 5232 973 |
      | user_bic              | BINAADADXXX                       |
    And I select hidden "user_birthday_day" with value "28"
    And I select hidden "user_birthday_month" with value "May"
    And I select hidden "user_birthday_year" with value "1975"
    And I select hidden "user_nationality" with value "France"
    And I select hidden "user_countryOfResidence" with value "France"
    And I press "Update"
    And I wait 500 ms for Jquery loading
    Then I should be on the "user dashboard profile edit payment" page
    And I should see "SUCCESS! Your payment informations has been modified successfully" in the "div.flashes div.alert" element

  Scenario: Wrong IBAN
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I follow "Payment information"
    And I fill in the following:
      | user_lastName         | MyLastName            |
      | user_firstName        | MyFirstName           |
      | user_profession       | MyProfession          |
      | user_annualIncome     | 1000                  |
      | user_bankOwnerName    | My Bank Owner Name    |
      | user_bankOwnerAddress | My Bank Owner Address |
      | user_iban             | 1234567890            |
      | user_bic              | BINAADADXXX           |
    And I select hidden "user_birthday_day" with value "28"
    And I select hidden "user_birthday_month" with value "May"
    And I select hidden "user_birthday_year" with value "1975"
    And I select hidden "user_nationality" with value "France"
    And I select hidden "user_countryOfResidence" with value "France"
    And I press "Update"
    Then I should be on the "user dashboard profile edit payment" page
    And I should see "An error has occurred." in the "div.flashes div.alert" element

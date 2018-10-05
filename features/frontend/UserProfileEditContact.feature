@default @javascript

Feature: User profile contact edit
  As a user i want to edit my contact info

  Scenario: Check required fields
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I follow "Contact information"
    And I fill in the following:
      | user_email |  |
    And I press "Update"
    Then I should be on the "user dashboard profile edit contact" page
    And I should see "An error has occurred." in the "div.flashes div.alert" element

  Scenario: Edit all fields
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I follow "Contact information"
    And I fill in the following:
      | user_email               | username@cocorico.rocks            |
      | user_phonePrefix         | 14                                 |
      | user_phone               | 123456                             |
      | user_addresses_0_address | 36-40 Rue Notre Dame des Victoires |
      | user_addresses_0_city    | Paris                              |
      | user_addresses_0_zip     | 75002                              |
    And I select hidden "user_addresses_0_country" with value "France"
    And I press "Update"
    And I wait 1000 ms
    Then I should be on the "user dashboard profile edit contact" page
    And I should see "SUCCESS! Your contact informations has been modified successfully" in the "div.flashes div.alert" element
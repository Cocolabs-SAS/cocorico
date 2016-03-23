@default @javascript

Feature: User profile about me edit
  As a offerer i want to edit my profile

  Scenario: As a offerer i can't update my profile without filling required fields
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I press "Update"
    Then I should be on the "user dashboard profile edit about me" page
    And I should see "An error has occurred." in the "div.flashes div.alert" element

  Scenario: As a offerer i can set my profile picture and description
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I wait 500 ms for Jquery loading
    And I attach the file "images/profile.png" to "user_image_new"
    And I wait 2000 ms
    And I fill in the following:
      | user_translations_en_description | My profile description |
    And I press "Update"
    Then I should be on the "user dashboard profile edit about me" page
    And I wait 3000 ms
    And I should see "SUCCESS! Your profile has been modified successfully" in the "div.flashes div.alert" element

  Scenario: As a offerer i can change my mother tongue, my spoken languages and set my description in both languages
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Profile" of "offerer"
    And I wait 500 ms for Jquery loading
    Then I should be on the "user dashboard profile edit about me" page
    When I select hidden "user_motherTongue" with value "English"
    And I select hidden "user_language" with value "Spanish"
    And I press "Add"
    And I wait 3000 ms
    Then I should see "Spanish" in the "ul.languages" element
    When I select hidden "user_language" with value "Chinese"
    And I press "Add"
    And I wait 1000 ms
    Then I should see "Chinese" in the "ul.languages" element
    When I fill in the following:
      | user_translations_en_description | My profile description |
    And I select hidden "user_fromLang" with value "English"
    And I select hidden "user_toLang" with value "French"
    And I click on the element with css selector "#btn-translate"
    And I wait 2000 ms
    And I click on the element with xpath selector "//a[@href='#fr']"
    Then the "user_translations_fr_description" field should contain "Ma description de profil"
    And I press "Update"
    Then I should be on the "user dashboard profile edit about me" page
    And I wait 3000 ms
    And I should see "SUCCESS! Your profile has been modified successfully" in the "div.flashes div.alert" element
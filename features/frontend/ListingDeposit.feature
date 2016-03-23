@listing @javascript

Feature: Listing deposit
  As a visitor I want to deposit a new listing so that i can rent it

  Scenario: As offerer i can deposit my new listing as logged in user
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    And I follow "Publish your listing"
    When I select category "Category1_1"
    #if your computer is too slow
    #And I wait 2000 ms
    And I fill in the following:
      | listing_translations_en_title       | My listing                  |
      | listing_translations_en_description | Beautiful and not expensive |
      | listing_translations_en_rules       | Don't smoke                 |
      | listing_price                       | 100                         |
    And I attach the file "images\listing.png" to "listing_image_new"
    And I select "France" from "Country"
    And I fill in the following:
      | City   | Paris          |
      | Zip    | 75002          |
      | Route  | rue beauregard |
      | Number | 16             |
    And I follow "Validate this address"
    And I check hidden "I accept the terms and conditions"
    And I press "Publish"
    And I wait 3000 ms
    Then I should be on the "dashboard listing edit presentation" page which "listing title" equal to "My listing"
    And I should see "SUCCESS! You have created a new listing successfully"
    And I should receive the "listing_activated_offerer" mail on "offerer@cocorico.rocks"
    When I click on the element with xpath selector "//a[@href='#fr']"
    And I wait 500 ms for Jquery loading
    Then the "listing_translations_fr_title" field should contain "Ma fiche"
    And the "listing_translations_fr_description" field should contain "Beau et pas cher"
    And the "listing_translations_fr_rules" field should contain "Ne fumez pas"


  Scenario: As a visitor can deposit my new listing while registering
    Given I am on the home page
    And I follow "Publish your listing"
    When I select category "Category1_1"
    #And I additionally select "Category2_2" from "listing_categories"
    And I fill in the following:
      | listing_translations_en_title       | My listing                  |
      | listing_translations_en_description | Beautiful and not expensive |
      | listing_translations_en_rules       | Don't smoke                 |
      | listing_price                       | 100                         |
    And I attach the file "images\listing.png" to "listing_image_new"
    And I select "United Kingdom" from "Country"
    And I fill in the following:
      | City                             | London                |
      | Zip                              | SW11 1DJ              |
      | Route                            | Lavender Gardens      |
      | Number                           | 49                    |
      | First name                       | Arnaud                |
      | Last name                        | Tsounabe              |
      | listing_user_email               | arnaud@cocorico.rocks |
      | listing_user_plainPassword_first | 12345678              |
      | Verification                     | 12345678              |
    And I follow "Validate this address"
    And I check hidden "I accept the terms and conditions"
    And I press "Publish"
    And I wait 3000 ms
    Then I should be on the "dashboard listing edit presentation" page which "listing title" equal to "My listing"
    And I should see "SUCCESS! You have created a new listing successfully"
    And I should receive the "registration" mail on "arnaud@cocorico.rocks"
    And I should receive the "listing_activated_offerer" mail on "arnaud@cocorico.rocks"
    When I click on the element with xpath selector "//a[@href='#fr']"
    And I wait 500 ms for Jquery loading
    Then the "listing_translations_fr_title" field should contain "Ma fiche"
    And the "listing_translations_fr_description" field should contain "Beau et pas cher"
    And the "listing_translations_fr_rules" field should contain "Ne fumez pas"

  Scenario: As offerer i can deposit my new listing while logging in
    Given I am on the home page
    And I follow "Publish your listing"
    When I select category "Category1_1"
    #And I wait 2000 ms
    And I fill in the following:
      | listing_translations_en_title       | My listing                  |
      | listing_translations_en_description | Beautiful and not expensive |
      | listing_translations_en_rules       | Don't smoke                 |
      | listing_price                       | 100                         |
    And I attach the file "images\listing.png" to "listing_image_new"
    And I select "France" from "Country"
    And I fill in the following:
      | City   | Paris          |
      | Zip    | 75002          |
      | Route  | rue beauregard |
      | Number | 16             |
    And I click on "#profile" tab
    #And I wait 1000 ms
    And I fill in the following:
      | listing_user_login__username | offerer@cocorico.rocks |
      | listing_user_login__password | 12345678               |
    And I follow "Validate this address"
    And I check hidden "I accept the terms and conditions"
    And I press "Publish"
    And I wait 3000 ms
    Then I should be on the "dashboard listing edit presentation" page which "listing title" equal to "My listing"
    And I should see "SUCCESS! You have created a new listing successfully"
    And I should receive the "listing_activated_offerer" mail on "offerer@cocorico.rocks"
    When I click on the element with xpath selector "//a[@href='#fr']"
    And I wait 500 ms for Jquery loading
    Then the "listing_translations_fr_title" field should contain "Ma fiche"
    And the "listing_translations_fr_description" field should contain "Beau et pas cher"
    And the "listing_translations_fr_rules" field should contain "Ne fumez pas"

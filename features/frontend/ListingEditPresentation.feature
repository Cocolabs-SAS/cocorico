@listing @javascript

Feature: Listing presentation edition
  As offerer i want to edit my listing's descriptions

  Scenario: As offerer i can edit my listing description in both language
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I wait 500 ms for Jquery loading
    When I fill in the following:
      | listing_translations_en_title       | My listing                  |
      | listing_translations_en_description | Beautiful and not expensive |
      | listing_translations_en_rules       | Don't smoke                 |
    And I select hidden "listing_fromLang" with value "English"
    And I select hidden "listing_toLang" with value "French"
    And I click on the element with css selector "#btn-translate"
    And I wait 3000 ms
    And I click on the element with xpath selector "//a[@href='#fr']"
    And I wait 2000 ms
    Then the "listing_translations_fr_title" field should contain "Ma fiche"
    And the "listing_translations_fr_description" field should contain "Beau et pas cher"
    And the "listing_translations_fr_rules" field should contain "Ne fumez pas"
    And I press button with css ".form-area [type=submit]"
    And I wait 3000 ms
    Then I should be on the "dashboard listing edit presentation" page which "listing title" equal to "My listing"
    And I should see "Update successful"
@listing @javascript

Feature: Listing characteristics edition
  As offerer i want to edit my listing's characteristics

  Scenario: As offerer i can edit my listing's characteristics
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Characteristics"
    When I select hidden "listing_listingListingCharacteristicsOrderedByGroup_0_listingCharacteristicValue" with value "No"
    And I select hidden "listing_listingListingCharacteristicsOrderedByGroup_1_listingCharacteristicValue" with value "3"
    And I select hidden "listing_listingListingCharacteristicsOrderedByGroup_2_listingCharacteristicValue" with value "Custom value 1"
    And I press button with css ".form-area [type=submit]"
    And I wait 500 ms for Jquery loading
    Then I should be on the "dashboard listing edit characteristic" page which "listing title" equal to "Listing One"
    And I should see "Update successful"
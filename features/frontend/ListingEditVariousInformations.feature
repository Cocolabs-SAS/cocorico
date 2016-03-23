@listing @javascript
Feature: Listing various informations edition
  As offerer i want to edit location and various informations of my listing

  Scenario: As offerer i can edit location and various informations of my listing
    Given I am on the home page
    And I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Various information"
    And I select hidden "listing_type" with value "Type two"
    And I select category "Category1_2"
    And I select hidden "listing_location_country" with value "France"
    And I fill in the following:
      | City   | Paris          |
      | Zip    | 75002          |
      | Route  | rue beauregard |
      | Number | 16             |
    And I follow "Validate this address"
    And I wait 500 ms for Jquery loading
    And I press button with css ".form-area button[type=submit]"
    And I wait 500 ms for Jquery loading
    Then I should be on the "dashboard listing edit various information" page which "listing title" equal to "Listing One"
    And I should see "Update successful"
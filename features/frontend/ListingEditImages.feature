@listing @javascript

Feature: Listing images edition
  As offerer I want to edit my listing's images

  Scenario: As offerer i cannot delete the last image from my listing
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Photos"
    And I click on the element with css selector "#listing_images a.remove"
    And I click on the element with css selector "#listing_images a.remove"
    And I wait 500 ms for Jquery loading
    And I press button with css ".form-area [type=submit]"
    And I wait 500 ms for Jquery loading
    Then I should be on the "dashboard listing edit images" page which "listing title" equal to "Listing One"
    And I should see "At least 1 images needed"

  Scenario: As offerer i can add an image to my listing
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Photos"
    And I attach the file "images\listing.png" to "listing_image_new"
    And I wait 3000 ms
    And I press button with css ".form-area [type=submit]"
    And I wait 3000 ms
    Then I should be on the "dashboard listing edit images" page which "listing title" equal to "Listing One"
    And I should see "Update successful"

  #todo: verify that images are really deleted
  Scenario: As offerer i can delete an image from my listing
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Photos"
    And I click on the element with css selector "#listing_images a.remove"
    And I wait 1000 ms
    And I press button with css ".form-area [type=submit]"
    And I wait 1000 ms
    Then I should be on the "dashboard listing edit images" page which "listing title" equal to "Listing One"
    And I should see "Update successful"
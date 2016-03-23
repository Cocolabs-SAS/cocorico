@listing @javascript

Feature: Listing price and conditions edition
  As offerer i want to edit my listing's price and conditions

  Scenario: As offerer i can set my listing default price
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Price & conditions"
    Then I should be on the "dashboard listing edit availabilities prices" page which "listing title" equal to "Listing One"
    When I fill in "listing_price" with "200"
    And I press button with css "#price-form-container [type=submit]"
    And I wait 2000 ms
    Then I should see "Saved"

  Scenario: As offerer i can add a discount
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Price & conditions"
    And I click on the element with css selector ".form-area a.add"
    And I wait 1000 ms
    And I fill in the following:
      | decrement      | 50 |
      | per-day-price2 | 50 |
    And I press button with css "#discount-form-container [type=submit]"
    And I wait 2000 ms
    Then I should be on the "dashboard listing edit availabilities prices" page which "listing title" equal to "Listing One"
    And I should see "Saved"


  Scenario: As offerer i can set cancellation policy and durations
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Price & conditions"
    And I select hidden "listing_min_duration" with value "4"
    And I select hidden "listing_max_duration" with value "30"
    And I select hidden "listing_cancellation_policy" with value "Strict"
    And I press button with css "#duration-form-container [type=submit]"
    And I wait 2000 ms
    Then I should be on the "dashboard listing edit availabilities prices" page which "listing title" equal to "Listing One"
    And I should see "Saved"


  Scenario: As offerer i can set prices for some of my listing availabilities days
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Price & conditions"
    And I wait 500 ms for Jquery loading
    And I fill date range with the following:
      | listing_availabilities_prices_date_range_start | 5 |
      | listing_availabilities_prices_date_range_end   | 8 |
    And I check hidden "Monday"
    And I check hidden "Tuesday"
    And I check hidden "Wednesday"
    And I check hidden "Thursday"
    And I check hidden "Friday"
    And I check hidden "Saturday"
    And I fill in the following:
      | listing_availabilities_prices_price_custom | 50 |
    And I press button with css "#prices-form [type=submit]"
    And I wait 500 ms for Jquery loading
    Then I should be on the "dashboard listing edit availabilities prices" page which "listing title" equal to "Listing One"
    And I should see "Success! Update successful"


  Scenario: As offerer i can set the price of one of my listing availability day
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Price & conditions"
    And I click on the element with css selector ".fc-day-number.fc-future"
    And I wait 2000 ms
    And I fill in the following:
      | listing_availability_price | 50 |
    And I press button with css "#availability-form-container button[type=submit]"
    And I wait 2000 ms
    Then I should see "Saved"


#  todo: Scenario: I can make some prices simulation of my listing
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Price & conditions"
    And I wait 1000 ms
    When I fill date range with the following:
      | start-date | 5 |
      | end-date   | 6 |
    And I wait 1000 ms
    Then I should see "100" in the "#price-simulator-form strong.price" element
    And I should see "You will earn €95" in the "#price-simulator-form .price-info" element
    And I should see "Our fee €5 " in the "#price-simulator-form .price-info" element

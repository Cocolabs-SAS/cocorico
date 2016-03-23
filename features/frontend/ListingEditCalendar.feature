@listing @javascript

Feature: Listing availabilities status edition
  As offerer i want to edit my listing's availabilities status

  Scenario: As offerer i can make unavailable some of my listing availabilities days
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Calendar"
    When I fill date range with the following:
      | listing_availabilities_status_date_range_start | 5 |
      | listing_availabilities_status_date_range_end   | 7 |
    And I check hidden "Monday"
    And I check hidden "Tuesday"
    And I check hidden "Wednesday"
    And I check hidden "Thursday"
    And I check hidden "Friday"
    And I check hidden "Saturday"
    And I select hidden "listing_availabilities_status_status" with value "Unavailable"
    And I press button with css ".form-area [type=submit]"
    And I wait 500 ms for Jquery loading
    Then I should be on the "dashboard listing edit availabilities status" page which "listing title" equal to "Listing One"
    And I should see "Update successful"


  Scenario: As offerer i can make unavailable one of my listing availability day
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Listings" of "offerer"
    And I follow "Edit"
    And I follow "Calendar"
    And I click on the element with css selector ".fc-day-number.fc-future"
    And I wait 3000 ms
    And I select hidden "listing_availability_status" with value "Unavailable"
    And I press button with css "#availability-form-container button[type=submit]"
    And I wait 3000 ms
    Then I should see "Saved"


#  todo: Make different scenario with time unit different of day
#  Scenario: I can make unavailable some of my listing availabilities days and times
#    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
#    And I follow "Listings"
#    And I follow "Edit"
#    And I follow "Calendar"
#    When I fill date range with the following:
#      | listing_availabilities_date_range_start | 5 |
#      | listing_availabilities_date_range_end   | 7 |
#    And I check "Monday"
#    And I check "Tuesday"
#    And I check "Wednesday"
#    And I check "Thursday"
#    And I check "Friday"
#    And I check "Saturday"
#    And I click on the element with css selector "#listing_availabilities_time_ranges a.add"
#    And I select "02" from "listing_availabilities_time_ranges_0_start_hour"
#    And I select "04" from "listing_availabilities_time_ranges_0_end_hour"
#    And I select "Unavailable" from "listing_availabilities_status"
#    And I press button with css "#content-dashboard [type=submit]"
#    And I wait 1000 ms
#    Then I should be on the "listing edit availabilities" page which "title" equal to "Listing One"
#    And I should see "Update successful"


@listing @javascript

# Todo: test times
Feature: Listing search
  As a user i want to find listing so that i can rent it

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 3   | 2      | 10000 |

  Scenario: I can find listings by location, categories, characteristics, date and price
    Given I do a search on the home page
    Then I should be on the "listing search result" page
    And I should see "1 results"
    When I fill date range with the following:
      | date_range_start | 4 |
      | date_range_end   | 5 |
    And I drag range slider ".range-box .ui-slider" with min equal to "40" and max equal to "400"
    And I select a characteristic "Characteristic_3" with value "Custom value 1"
#    And I press "Search"
    And I wait 500 ms for Jquery loading
    Then I should be on the "listing search result" page
    And I should see "1 results"

  Scenario: I can not find listings without searched categories
    Given I do a search on the home page
    Then I should be on the "listing search result" page
    And I should see "1 results"
    When I select categories "Category2_2"
    And I select a characteristic "Characteristic_3" with value "Custom value 1"
#    And I press "Search"
    And I wait 500 ms for Jquery loading
    Then I should be on the "listing search result" page
    And I should see "0 results"

    #If Characteristic_2 => element is not visible and there is a failure. hidden class management isn't declared for this select yet ?
  Scenario: I can not find listings without searched characteristics
    Given I do a search on the home page
    Then I should be on the "listing search result" page
    And I should see "1 results"
    When I select a characteristic "Characteristic_3" with value "Custom value 2"
#    And I press "Search"
    And I wait 500 ms for Jquery loading
    Then I should be on the "listing search result" page
    And I should see "0 results"

  Scenario: I can not find listings unavailable
    Given I do a search on the home page
    Then I should be on the "listing search result" page
    And I should see "1 results"
    When I fill date range with the following:
      | date_range_start | 2 |
      | date_range_end   | 4 |
    And I select a characteristic "Characteristic_3" with value "Custom value 1"
#    And I press "Search"
    And I wait 500 ms for Jquery loading
    Then I should be on the "listing search result" page
    And I should see "0 results"

  Scenario: I cannot find listings with price out of searched price range
    Given I do a search on the home page
    Then I should be on the "listing search result" page
    And I should see "1 results"
    When I fill date range with the following:
      | date_range_start | 4 |
      | date_range_end   | 5 |
    And I drag range slider ".range-box .ui-slider" with min equal to "150" and max equal to "300"
    And I select a characteristic "Characteristic_3" with value "Custom value 1"
    And I wait 500 ms for Jquery loading
#    And I press "Search"
    Then I should be on the "listing search result" page
    And I should see "0 results"

#To activate if flexibility search is enable
#  Scenario: I can find listings unavailable with a big flexibility
#    Given I do a search on the home page
#    Then I should be on the "listing search result" page
#    And I should see "1 results"
#    #Flexibility
#    When I select a characteristic "flexibility" with value "3"
#    And I fill date range with the following:
#      | date_range_start | 1 |
#      | date_range_end   | 3 |
#    And I press "Search"
#    And I wait 1000 ms
#    Then I should be on the "listing search result" page
#    And I should see "1 results"

#To activate if flexibility search is enable
#  Scenario: I cannot find listings unavailable with a small flexibility
#    Given I do a search on the home page
#    Then I should be on the "listing search result" page
#    And I should see "1 results"
#    #Flexibility
#    When I select a characteristic "flexibility" with value "1"
#    #Date range
#    And I fill date range with the following:
#      | date_range_start | 1 |
#      | date_range_end   | 3 |
#    And I press "Search"
#    And I wait 1000 ms
#    Then I should be on the "listing search result" page
#    And I should see "0 results"

# Todo: Make different scenario with time unit different of day
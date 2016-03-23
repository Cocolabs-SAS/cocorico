@booking @javascript

Feature: Reviews and rates
  As a user i want to show and add rates and reviews

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 1   | 1      | 15000 |
      | Listing One  | 2   | 1      | 15000 |
    #Same scenario than "As an asker i can find a listing so that i can make a booking request"
    And I make this new booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | listingTitle        | Listing One            |
      | start               | 1                      |
      | end                 | 3                      |
      | start_time          | 00:00                  |
      | end_time            | 00:00                  |
      | amountExpected      | 300                    |
      | feesExpected        | 30                     |
      | totalAmountExpected | 330                    |
    #Same scenario than "As an offerer i can accept a booking request"
    And An offerer accept my booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | totalAmountExpected | 285                    |
    When System user run "cocorico:bookings:validate" command
    Then He should see "1 booking(s) validated" on console
    And He should receive the "reminder_to_rate_asker_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "reminder_to_rate_offerer_asker" mail on "asker@cocorico.rocks"

  Scenario: As offerer i can add a rating to an asker
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Comments" of "offerer"
    Then I should be on the "dashboard reviews received" page
    And I wait 500 ms for Jquery loading
    And I should see "You must make notation for booking" in the "div.area" element
    When I follow "Add your rating"
    And I wait 500 ms for Jquery loading
    And I click on the element with css selector "#user-rating-make a"
    And I fill in the following:
      | comment | Nice asker |
    And I press "Publish this comment"
    And I wait 1000 ms
    Then I should be on the "dashboard reviews added" page

  Scenario: As asker i can add a rating to an offerer so that i can see my rating listing and user page
    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Comments" of "asker"
    Then I should be on the "dashboard reviews received" page
    And I wait 500 ms for Jquery loading
    And I should see "You must make notation for booking" in the "div.area" element
    And I follow "Add your rating"
    And I wait 500 ms for Jquery loading
    When I click on the element with css selector "#user-rating-make a"
    And I fill in the following:
      | comment | Nice listing and offerer |
    And I press "Publish this comment"
    And I wait 1000 ms
    Then I should be on the "dashboard reviews added" page
    When I do a search on the home page
    And I follow "Category1_1,"
    And I wait 500 ms for Jquery loading
    Then I should be on the "listing show" page which "listing title" equal to "Listing One"
    When I click on "#comments" tab
    And I wait 2000 ms
    Then I should see "Nice listing and offerer" in the "div.posts-holder" element
    When I follow "OffererFirstName"
    And I wait 500 ms for Jquery loading
    Then I should be on the "user profile show" page which "email" equal to "offerer@cocorico.rocks"
    And I should see "Comments (1)" in the "div.head" element
    And I should see an ".rating" element
    And I wait 2000 ms
    And I should see "Nice listing and offerer" in the "div.posts-holder" element

  Scenario: As user i can't add a rating without filling required fields
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Comments" of "offerer"
    Then I should be on the "dashboard reviews received" page
    And I wait 500 ms for Jquery loading
    And I should see "You must make notation for booking" in the "div.area" element
    When I follow "Add your rating"
    And I press "Publish this comment"
    And I wait 2000 ms
    Then I should see "An error has occurred." in the "div.flashes div.alert" element

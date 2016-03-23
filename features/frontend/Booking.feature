@booking @spool @javascript

Feature: Booking
  As an asker i want to make a booking request

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 4   | 1      | 15000 |
      | Listing One  | 5   | 1      | 15000 |


  Scenario: As asker i can find a listing so that i can make a booking request
    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
    And I do a search on the home page
    And I find a listing which "listing title" equal to "Listing One"

    #When I am on the "listing show" page which "listing title" equal to "Listing One"

    #Wait ajax booking price result
    And I wait 3000 ms
    When I fill date range with the following:
      | start-date | 4 |
      | end-date   | 6 |
    #Wait ajax booking price result
    And I wait 3000 ms
    Then I should see "300" in the ".add-info .price" element
    When I follow "Book now"
    Then I should be on this new booking page:
      | listingTitle | Listing One |
      | start        | 4           |
      | end          | 6           |
      | start_time   | 00:00       |
      | end_time     | 00:00       |
    And I should see "300" in the "[data-id=booking-amount]" element
    And I should see "30" in the "[data-id=booking-fees]" element
    And I should see "330" in the "[data-id=booking-total]" element
    When I fill in "message" with "Hello, I want to book your listing"
    And I fill correctly credit card informations
    And I check hidden "I accept the terms of use"
    And I press "Validate booking"
    And I eventually retry new booking request if first card failed
    Then I should be on "https://3ds-acs.test.modirum.com/mdpayacs/pareq"
    When I fill in "password" with "secret3"
    And I press "Submit"
    Then I should be on the page which route name is "dashboard_booking_show_asker"
    And I should see "330" in the ".post [data-id=booking-total]" element
    And I should receive the "booking_request_asker" mail on "asker@cocorico.rocks"
    And He should receive the "booking_request_offerer" mail on "offerer@cocorico.rocks"
    And I should see "SUCCESS! You have booked this listing successfully"


  Scenario: As asker i can cancel a booking request not already accepted by offerer
   #Same scenario than "As an asker i can find a listing so that i can make a booking request"
    Given I make this new booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | listingTitle        | Listing One            |
      | start               | 4                      |
      | end                 | 6                      |
      | start_time          | 00:00                  |
      | end_time            | 00:00                  |
      | amountExpected      | 300                    |
      | feesExpected        | 30                     |
      | totalAmountExpected | 330                    |
    When I follow "Cancel"
    And I wait 3000 ms
    Then I should see "0" in the ".modal-body [data-id=booking-total]" element
    When I fill in "booking_message" with "Finally i have to cancel my booking request"
    And I check hidden "I accept the terms of use"
    And I click on the element with css selector "#booking-edit-submit"
    And I wait 3000 ms
    Then I should see "SUCCESS!"
    And I should receive the "booking_canceled_by_asker_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "booking_canceled_by_asker_asker" mail on "asker@cocorico.rocks"


  Scenario: As offerer i can accept a booking request
    #Same scenario than "As an asker i can find a listing so that i can make a booking request"
    Given An asker make this new booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | listingTitle        | Listing One            |
      | start               | 4                      |
      | end                 | 6                      |
      | start_time          | 00:00                  |
      | end_time            | 00:00                  |
      | amountExpected      | 300                    |
      | feesExpected        | 30                     |
      | totalAmountExpected | 330                    |
    And I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Bookings" of "offerer"
    Then I should be on the "dashboard booking offerer" page
    And I should see "1 result"
    When I follow "Show"
    Then I should be on the page which route name is "dashboard_booking_show_offerer"
    And I should see "285" in the ".post [data-id=booking-total]" element
    When I follow "Accept"
    And I wait 3000 ms
    Then I should see "285" in the ".modal-body [data-id=booking-total]" element
    When I fill in "booking_message" with "Ok let's go!"
    And I check hidden "I accept the terms of use"
    And I click on the element with css selector "#booking-edit-submit"
    And I wait 20000 ms
    Then I should see "SUCCESS!"
    And I should receive the "booking_accepted_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "booking_accepted_asker" mail on "asker@cocorico.rocks"


  Scenario: As offerer i can refuse a booking request
    #Same scenario than "As an asker i can find a listing so that i can make a booking request"
    Given An asker make this new booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | listingTitle        | Listing One            |
      | start               | 4                      |
      | end                 | 6                      |
      | start_time          | 00:00                  |
      | end_time            | 00:00                  |
      | amountExpected      | 300                    |
      | feesExpected        | 30                     |
      | totalAmountExpected | 330                    |
    And I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Bookings" of "offerer"
    Then I should be on the "dashboard booking offerer" page
    And I should see "1 result"
    When I follow "Show"
    Then I should be on the page which route name is "dashboard_booking_show_offerer"
    And I should see "285" in the ".post [data-id=booking-total]" element
    When I follow "Decline"
    And I wait 3000 ms
    Then I should see "285" in the ".modal-body [data-id=booking-total]" element
    When I fill in "booking_message" with "Sorry, my listing is no more available"
    And I check hidden "I accept the terms of use"
    And I click on the element with css selector "#booking-edit-submit"
    And I wait 3000 ms
    Then I should see "SUCCESS!"
    And I should receive the "booking_refused_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "booking_refused_asker" mail on "asker@cocorico.rocks"


  Scenario: As asker i can cancel a booking request already accepted by offerer
    #Same scenario than "As an asker i can find a listing so that i can make a booking request"
    Given I make this new booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | listingTitle        | Listing One            |
      | start               | 4                      |
      | end                 | 6                      |
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
    When I am logged in as user "asker@cocorico.rocks" with password "12345678"
    And I click on dashboard menu "Bookings" of "asker"
    Then I should be on the "dashboard booking asker" page
    And I should see "1 result"
    When I follow "Show"
    Then I should be on the page which route name is "dashboard_booking_show_asker"
    And I should see "330" in the ".post [data-id=booking-total]" element
    When I follow "Cancel"
    And I wait 3000 ms
    Then I should see "285" in the ".modal-body [data-id=booking-total]" element
    And I should see "Flexible" in the ".modal-body [data-id=booking-policy]" element
    When I fill in "booking_message" with "Finally i have to cancel my booking request"
    And I check hidden "I accept the terms of use"
    And I click on the element with css selector "#booking-edit-submit"
    And I wait 29000 ms
    Then I should see "SUCCESS!"
    And I should receive the "booking_canceled_by_asker_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "booking_canceled_by_asker_asker" mail on "asker@cocorico.rocks"


  Scenario: As asker i can't send a booking message without filling required fields
    Given I make this new booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | listingTitle        | Listing One            |
      | start               | 4                      |
      | end                 | 6                      |
      | start_time          | 00:00                  |
      | end_time            | 00:00                  |
      | amountExpected      | 300                    |
      | feesExpected        | 30                     |
      | totalAmountExpected | 330                    |
    And I press "Save"
    Then I should be on the page which route name is "dashboard_booking_show_asker"
    And I should see "An error has occurred." in the "div.flashes div.alert" element


  Scenario: As asker i can send a booking message
    Given I make this new booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | listingTitle        | Listing One            |
      | start               | 4                      |
      | end                 | 6                      |
      | start_time          | 00:00                  |
      | end_time            | 00:00                  |
      | amountExpected      | 300                    |
      | feesExpected        | 30                     |
      | totalAmountExpected | 330                    |
    When I fill in the following:
      | message_body | I forgot to tell you that i have a dog |
    And I press "Save"
    Then I should be on the page which route name is "dashboard_booking_show_asker"
    And I wait 2000 ms
    And I should see "I forgot to tell you that i have a dog" in the "div.blog div.posts-holder" element



#todo: To complete
#  Scenario: Change status and check bookings listing
#    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
#    When I click on dashboard menu "Bookings" of "offerer"
#    Then I should be on the "dashboard booking offerer" page
#    And I wait 500 ms for Jquery loading
#    When I select hidden "status" with value "New"
#    Then I should be on the "dashboard booking offerer" page
#    When I select hidden "status" with value "Payed"
#    Then I should be on the "dashboard booking offerer" page
#    When I select hidden "status" with value "Expired"
#    Then I should be on the "dashboard booking offerer" page
#    When I select hidden "status" with value "Refused"
#    Then I should be on the "dashboard booking offerer" page
#    When I select hidden "status" with value "Canceled by asker"
#    Then I should be on the "dashboard booking offerer" page
#    When I select hidden "status" with value "Canceled by offerer"
#    Then I should be on the "dashboard booking offerer" page
#    When I select hidden "status" with value "Payment refused"
#    Then I should be on the "dashboard booking offerer" page



#  Scenario: test scenario
#    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
#    When I click on dashboard menu "Bookings" of "asker"
#    Then I should be on the "dashboard booking asker" page
#    And I should see "330" in the ".post span[data-id=booking-total]" element
#    When I follow "Show"
#    Then I should be on the page which route name is "dashboard_booking_show_asker"
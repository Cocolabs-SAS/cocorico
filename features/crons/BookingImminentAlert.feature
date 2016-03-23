@booking @javascript

Feature: Bookings imminent alert command
  As a system user i want to alert imminent bookings so that offerer and asker know that a booking is going to start

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 1   | 1      | 15000 |
      | Listing One  | 2   | 1      | 15000 |


  Scenario: I can alert offerer and asker that a booking is going to start
    Given An asker make this new booking request:
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
    And An offerer accept my booking request:
      | askerEmail          | asker@cocorico.rocks   |
      | askerPassword       | 12345678               |
      | offererEmail        | offerer@cocorico.rocks |
      | offererPassword     | 12345678               |
      | totalAmountExpected | 285                    |
    When I run "cocorico:bookings:alertImminent" command
    Then I should see "1 booking(s) imminent alerted" on console
    And He should receive the "booking_imminent_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "booking_imminent_asker" mail on "asker@cocorico.rocks"

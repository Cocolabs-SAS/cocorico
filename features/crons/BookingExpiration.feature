@booking @javascript

Feature: Bookings expiration command
  As a system user i want to expire bookings so that bookings request not accepted or not refused are expired

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 1   | 1      | 15000 |
      | Listing One  | 2   | 1      | 15000 |


  Scenario: I can expire booking not accepted or not refused by the offerer for some time
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

    When I run "cocorico:bookings:expire" command
    Then I should see "1 booking(s) expired" on console
    And He should receive the "booking_request_expired_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "booking_request_expired_asker" mail on "asker@cocorico.rocks"

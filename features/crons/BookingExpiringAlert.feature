@booking @javascript

Feature: Bookings expiring alert command
  As a system user i want to alert offerer having expiring bookings so that offerer can accept or refuse them

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 1   | 1      | 15000 |
      | Listing One  | 2   | 1      | 15000 |


  Scenario: I can alert offerer having expiring bookings
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

    When I run "cocorico:bookings:alertExpiring" command
    Then I should see "1 booking(s) expiring alerted" on console
    And He should receive the "booking_request_expiration_alert_offerer" mail on "offerer@cocorico.rocks"

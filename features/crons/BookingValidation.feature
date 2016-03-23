@booking @javascript

Feature: Bookings validation command
  As a system user i want to validate payed bookings started from some time so that users can rate each others
  (and offerers can be payed)

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 1   | 1      | 15000 |
      | Listing One  | 2   | 1      | 15000 |


  Scenario: I can validate payed bookings started for some time so that offerer and asker can rate each others
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
    When I run "cocorico:bookings:validate" command
    Then I should see "1 booking(s) validated" on console
    And He should receive the "reminder_to_rate_asker_offerer" mail on "offerer@cocorico.rocks"
    And He should receive the "reminder_to_rate_offerer_asker" mail on "asker@cocorico.rocks"

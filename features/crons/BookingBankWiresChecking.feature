@booking @javascript

Feature: Bookings bank wires checking command
  As a system user i want to check booking bank wires so that i can know if an offerer has been paid

  Background:
    Given there are following availabilities:
      | listingTitle | day | status | price |
      | Listing One  | 1   | 1      | 15000 |
      | Listing One  | 2   | 1      | 15000 |

  Scenario: I can check that a bank wire has been transferred to an offerer after a booking has been payed
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
    #Offerer bank account
    When I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    And I click on dashboard menu "Profile" of "offerer"
    And I follow "Payment information"
    And I fill in the following:
      | user_lastName         | MyLastName                        |
      | user_firstName        | MyFirstName                       |
      | user_profession       | MyProfession                      |
      | user_annualIncome     | 1000                              |
      | user_bankOwnerName    | My Bank Owner Name                |
      | user_bankOwnerAddress | My Bank Owner Address             |
      | user_iban             | FR76 1790 6000 3200 0833 5232 973 |
      | user_bic              | BINAADADXXX                       |
    And I select hidden "user_birthday_day" with value "29"
    And I select hidden "user_birthday_month" with value "May"
    And I select hidden "user_birthday_year" with value "1971"
    And I select hidden "user_nationality" with value "France"
    And I select hidden "user_countryOfResidence" with value "France"
    And I press "Update"
    And I wait 500 ms for Jquery loading
    Then I should be on the "user dashboard profile edit payment" page
    And I should see "SUCCESS! Your payment informations has been modified successfully" in the "div.flashes div.alert" element
    #Booking validations
    When I run "cocorico:bookings:validate" command
    Then I should see "1 booking(s) validated" on console
    #Bank wire
    When I am logged in on admin as user "super-admin@cocorico.rocks" with password "super-admin"
    And Admin make a bank wire on offerer bank account
    And I run "cocorico:bookings:checkBankWires" command
    Then I should see "1 booking(s) bank wires checked" on console
    And He should receive the "booking_bank_wire_transfer_offerer" mail on "offerer@cocorico.rocks"
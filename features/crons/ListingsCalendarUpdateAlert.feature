@command

Feature: Listings calendar update alert command
  As a system user i want to alert offerers to update their listing calendars

  Scenario: I can alert offerers to update their listing calendars
    When I run "cocorico:listings:alertUpdateCalendars" command
    Then I should see "1 listing(s) calendar update alerted" on console
    And He should receive the "update_your_calendar_offerer" mail on "offerer@cocorico.rocks"
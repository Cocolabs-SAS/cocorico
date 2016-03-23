@command

Feature: Listings notation computing command
  As a system user i want to compute listings notations

  Scenario: I can alert compute listings notations
    When I run "cocorico_listing_search:computeNotation" command
    Then I should see "1 listing(s) notation computed" on console
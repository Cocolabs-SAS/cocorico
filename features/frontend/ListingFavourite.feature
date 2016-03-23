@listing @javascript

Feature: Listing favourite
  As a user I can add and remove a listing from my favourites

  Scenario: I can check favourite listing
    Given I do a search on the home page
    And I click on the element with css selector ".favourit"
    And I should see an ".favourit.active" element
    And I follow "Category1_1,"
    And I wait 500 ms for Jquery loading
    Then I should be on the "listing show" page which "listing title" equal to "Listing One"
    And I should see an ".link-favourit.favourit.active" element
    When I follow "Favorites"
    Then I should be on the "listing favourite" page
    And I should see an ".favourit.active" element
    When I click on the element with css selector ".favourit.active"
    And I follow "Favorites"
    Then I should see "(0 results)" in the "h1" element
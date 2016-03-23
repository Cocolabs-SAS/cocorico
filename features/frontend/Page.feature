@default @javascript

Feature: Editorial pages content display
  As a user I want to see content of editorial pages

  Scenario: I can see editorial pages content in english
    Given I am on the home page
    And I follow "Who we are?"
    Then I should see "Who we are?" in the "div.container h1" element
    And I follow "How it works?"
    Then I should see "How it works?" in the "div.container h1" element
    And I follow "Legal notices"
    Then I should see "Legal notices" in the "div.container h1" element
    And I follow "FAQ"
    Then I should see "FAQ" in the "div.container h1" element

  Scenario: I can see editorial pages content in french
    Given I am on the home page
    When I click on the element with css selector ".link-flag"
    And I follow "Fr"
    And I wait 500 ms for Jquery loading
    And I follow "Qui sommes nous?"
    Then I should see "Qui sommes nous?" in the "div.container h1" element
    And I follow "Comment ca marche?"
    Then I should see "Comment ca marche?" in the "div.container h1" element
    And I follow "Mentions légales"
    Then I should see "Mentions légales" in the "div.container h1" element
    And I follow "FAQ"
    Then I should see "FAQ" in the "div.container h1" element
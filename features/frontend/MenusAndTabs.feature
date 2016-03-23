@default @javascript

Feature: Menus and tabs in dashboard
  As a user I want to check my menu and tabs

  Scenario: Check menu as offerer
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu of "offerer"
    Then I should see "Messages" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Bookings" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Listings" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Payments" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Comments" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Profile" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Logout" in the "li.dropdown ul.dropdown-menu" element


  Scenario: Check tabs as offerer
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Bookings" of "offerer"
    Then I should be on the "dashboard booking offerer" page
    And I should see "Messages" in the "nav.tabset-holder" element
    And I should see "Bookings" in the "nav.tabset-holder" element
    And I should see "Listings" in the "nav.tabset-holder" element
    And I should see "Payments" in the "nav.tabset-holder" element
    And I should see "Comments" in the "nav.tabset-holder" element
    And I should see "Profile" in the "nav.tabset-holder" element


  Scenario: Check menu as asker
    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
    When I click on dashboard menu of "asker"
    Then I should see "Messages" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Bookings" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Payments" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Comments" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Profile" in the "li.dropdown ul.dropdown-menu" element
    And I should see "Logout" in the "li.dropdown ul.dropdown-menu" element

  Scenario: Check tabs as asker
    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Messages" of "asker"
    Then I should be on the "dashboard message" page
    And I wait 500 ms for Jquery loading
    And I should see "Messages" in the "nav.tabset-holder" element
    And I should see "Bookings" in the "nav.tabset-holder" element
    And I should see "Payments" in the "nav.tabset-holder" element
    And I should see "Comments" in the "nav.tabset-holder" element
    And I should see "Profile" in the "nav.tabset-holder" element

  Scenario: Check profile switch
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Messages" of "offerer"
    Then I should be on the "dashboard message" page
    And I wait 500 ms for Jquery loading
    And I click on the element with css selector ".form-switchers label"

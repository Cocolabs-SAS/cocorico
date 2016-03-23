@default @javascript

Feature: Breadcrumb
  As a user I want to check my breadcrumbs

  Scenario: Check main tabs breadcrumb as offerer
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Messages" of "offerer"
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Messages" in the "ul.breadcrumb" element

    When I click on dashboard menu "Bookings" of "offerer"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Bookings" in the "ul.breadcrumb" element

    When I click on dashboard menu "Listings" of "offerer"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Listing" in the "ul.breadcrumb" element

    When I click on dashboard menu "Payments" of "offerer"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Payments" in the "ul.breadcrumb" element

    When I click on dashboard menu "Comments" of "offerer"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Comments" in the "ul.breadcrumb" element
    And I should see "Rating Received" in the "ul.breadcrumb" element

    When I click on the element with css selector ".form-radio span.jcf-unchecked"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Comments" in the "ul.breadcrumb" element
    And I should see "Rating Made" in the "ul.breadcrumb" element

    When I click on dashboard menu "Profile" of "offerer"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Profile" in the "ul.breadcrumb" element
    And I should see "About me" in the "ul.breadcrumb" element

    When I follow "Payment information"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Profile" in the "ul.breadcrumb" element
    And I should see "Payment information" in the "ul.breadcrumb" element

    When I follow "Contact information"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Profile" in the "ul.breadcrumb" element
    And I should see "Contact information" in the "ul.breadcrumb" element


  Scenario: Check all tab detail breadcrumb as offerer
    Given I am logged in as user "offerer@cocorico.rocks" with password "12345678"

    # listing tab and edit listing
    When I click on dashboard menu "Listings" of "offerer"
    Then I should be on the "dashboard listing" page
    And I wait 500 ms for Jquery loading
    When I follow "Edit"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Listing" in the "ul.breadcrumb" element
    And I should see "Listing One" in the "ul.breadcrumb" element
    And I should see "presentation" in the "ul.breadcrumb" element

    When I follow "Price & conditions"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Listing" in the "ul.breadcrumb" element
    And I should see "Listing One" in the "ul.breadcrumb" element
    And I should see "price and conditions" in the "ul.breadcrumb" element

    When I follow "Calendar"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Listing" in the "ul.breadcrumb" element
    And I should see "Listing One" in the "ul.breadcrumb" element
    And I should see "calendar" in the "ul.breadcrumb" element

    When I follow "Photos"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Listing" in the "ul.breadcrumb" element
    And I should see "Listing One" in the "ul.breadcrumb" element
    And I should see "photos" in the "ul.breadcrumb" element

    When I follow "Characteristics"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Listing" in the "ul.breadcrumb" element
    And I should see "Listing One" in the "ul.breadcrumb" element
    And I should see "characteristics" in the "ul.breadcrumb" element

    When I follow "Various information"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Offerer" in the "ul.breadcrumb" element
    And I should see "Listing" in the "ul.breadcrumb" element
    And I should see "Listing One" in the "ul.breadcrumb" element
    And I should see "various informations" in the "ul.breadcrumb" element

    # listing tab and show listing
    When I click on dashboard menu "Listings" of "offerer"
    Then I should be on the "dashboard listing" page
    And I wait 500 ms for Jquery loading
    When I follow "Show"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ol.breadcrumb" element
    And I should see "Pays" in the "ol.breadcrumb" element
    And I should see "Région" in the "ol.breadcrumb" element
    And I should see "Département" in the "ol.breadcrumb" element
    And I should see "Lieu dit" in the "ol.breadcrumb" element
    And I should see "Code postal" in the "ol.breadcrumb" element

#    # message tab
#    #Booking And relative messages Fixture have been disabled
#    When I click on dashboard menu "Messages" of "offerer"
#    Then I should be on the "contact message list" page
#    And I wait 500 ms for Jquery loading
#    When I follow "Reply"
#    And I wait 500 ms for Jquery loading
#    Then I should see "Home" in the "ul.breadcrumb" element
#    And I should see "Offerer" in the "ul.breadcrumb" element
#    And I should see "Messages" in the "ul.breadcrumb" element
#    And I should see "Discussion with AskerFirstName A." in the "ul.breadcrumb" element

#    # booking tab
#    When I click on dashboard menu "Bookings" of "offerer"
#    Then I should be on the "dashboard booking offerer" page
#    And I wait 500 ms for Jquery loading
#    When I follow "Show"
#    And I wait 500 ms for Jquery loading
#    Then I should see "Home" in the "ul.breadcrumb" element
#    And I should see "Offerer" in the "ul.breadcrumb" element
#    And I should see "Bookings" in the "ul.breadcrumb" element
#    And I should see "Listing One" in the "ul.breadcrumb" element

  Scenario: Check main tab breadcrumb as asker
    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
    When I click on dashboard menu "Messages" of "asker"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Messages" in the "ul.breadcrumb" element

    When I click on dashboard menu "Bookings" of "asker"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Bookings" in the "ul.breadcrumb" element

    When I click on dashboard menu "Payments" of "asker"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Payments" in the "ul.breadcrumb" element

    When I click on the element with css selector ".form-radio span.jcf-unchecked"
    Then I should be on the "dashboard booking payin refund asker" page
    And I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Payments" in the "ul.breadcrumb" element

    When I click on dashboard menu "Comments" of "asker"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Comments" in the "ul.breadcrumb" element
    And I should see "Rating Received" in the "ul.breadcrumb" element
    And I click on the element with css selector ".form-radio span.jcf-unchecked"
    And I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Comments" in the "ul.breadcrumb" element
    And I should see "Rating Made" in the "ul.breadcrumb" element

    When I click on dashboard menu "Profile" of "asker"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Profile" in the "ul.breadcrumb" element
    And I should see "About me" in the "ul.breadcrumb" element

    When I follow "Payment information"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Profile" in the "ul.breadcrumb" element
    And I should see "Payment information" in the "ul.breadcrumb" element

    When I follow "Contact information"
    And I wait 500 ms for Jquery loading
    Then I should see "Home" in the "ul.breadcrumb" element
    And I should see "Asker" in the "ul.breadcrumb" element
    And I should see "Profile" in the "ul.breadcrumb" element
    And I should see "Contact information" in the "ul.breadcrumb" element

#  Scenario: Check all tab detail breadcrumb as asker
#    #Booking And relative messages Fixture have been disabled
#    # message tab
#    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
#    When I click on dashboard menu "Messages" of "asker"
#    Then I should be on the "contact message list" page
#    And I wait 500 ms for Jquery loading
#    When I follow "Reply"
#    And I wait 500 ms for Jquery loading
#    Then I should see "Home" in the "ul.breadcrumb" element
#    And I should see "Asker" in the "ul.breadcrumb" element
#    And I should see "Messages" in the "ul.breadcrumb" element
#    And I should see "Discussion with OffererFirstName O." in the "ul.breadcrumb" element
#
#    # booking tab
#    When I click on dashboard menu "Bookings" of "asker"
#    Then I should be on the "dashboard booking asker" page
#    And I wait 500 ms for Jquery loading
#    When I follow "Show"
#    And I wait 500 ms for Jquery loading
#    Then I should see "Home" in the "ul.breadcrumb" element
#    And I should see "Asker" in the "ul.breadcrumb" element
#    And I should see "Bookings" in the "ul.breadcrumb" element
#    And I should see "Listing One" in the "ul.breadcrumb" element
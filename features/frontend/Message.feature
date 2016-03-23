@message @javascript

Feature: Messages management
  As a user i want to send, consult and reply to a message

  Scenario: As asker i can send a message to an offerer about his listing
    Given I am logged in as user "asker@cocorico.rocks" with password "12345678"
    And I do a search on the home page
    And I find a listing which "listing title" equal to "Listing One"
    And I click on the element with css selector "a.contact-opener"
    And I wait 2000 ms
    And I fill in "message_body" with "Hello, can i rent your listing?"
    And I press "Send"
    Then I should be on the page which route name is "dashboard_message_thread_view"
    When I wait 2000 ms
    Then I should see "Hello, can i rent your listing?" in the "div.posts-holder div.post-content" element

  Scenario: As a visitor i must be logged to send a message to an offerer about his listing
    Given I do a search on the home page
    And I find a listing which "listing title" equal to "Listing One"
    And I click on the element with css selector "a.contact-opener"
    Then I should be on the "user login" page
    When I fill in the following:
      | Email    | asker@cocorico.rocks |
      | Password | 12345678             |
    And I press "Login"
    Then I should be on the "listing show" page which "listing title" equal to "Listing One"
    And I click on the element with css selector "a.contact-opener"
    And I wait 2000 ms
    And I fill in "message_body" with "Hello, can i rent your listing?"
    And I press "Send"
    Then I should be on the page which route name is "dashboard_message_thread_view"
    When I wait 2000 ms
    Then I should see "Hello, can i rent your listing?" in the "div.posts-holder div.post-content" element

  Scenario: As offerer i can't reply to a new message without filling required fields
    #Same than scenario "As an asker i can send a message to an offerer about his listing"
    Given A user send me the message "Hello, can i rent your listing?" about my listing "Listing One"
    When I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    And I click on dashboard menu "Messages" of "offerer"
    Then I should be on the "dashboard message" page
    And I should see "1 result" in the "span.result-counter" element
    When I follow "Reply"
    And I press "Send"
    Then I should see "An error has occurred." in the "div.flashes div.alert" element


  Scenario: As offerer i can reply to a new message
    Given A user send me the message "Hello, can i rent your listing?" about my listing "Listing One"
    When I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    And I click on dashboard menu "Messages" of "offerer"
    Then I should be on the "dashboard message" page
    And I should see "1 result" in the "span.result-counter" element
    When I follow "Reply"
    And I fill in the following:
      | message_body | Yes! Of course |
    And I press "Send"
    Then I should be on the page which route name is "dashboard_message_thread_view"
    When I wait 2000 ms
    Then I should see "Yes! Of course" in the "div.posts-holder div.post-content" element


  Scenario: As offerer i can delete one of my messages
    Given A user send me the message "Hello, can i rent your listing?" about my listing "Listing One"
    When I am logged in as user "offerer@cocorico.rocks" with password "12345678"
    And I click on dashboard menu "Messages" of "offerer"
    Then I should be on the "dashboard message" page
    And I should see "1 result" in the "span.result-counter" element
    When I follow "Delete"
    Then I should be on the "dashboard message" page
    And I should see "No result" in the "span.result-counter" element
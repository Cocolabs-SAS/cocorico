<?php

class MessageContext extends CommonContext
{
    /**
     * Same than scenario "As an asker i can send a message to an offerer about his listing"
     *
     * @When /^I send message "(?P<message>[^"]+)" about listing "(?P<title>[^"]+)"$/
     * @When /^A user send me the message "(?P<message>[^"]+)" about my listing "(?P<title>[^"]+)"$/
     *
     * @param string $message
     * @param string $listingTitle
     */
    public function iSendAMessageAboutAListing($message, $listingTitle)
    {
        $this->userContext->iAmLoggedInUser('asker@cocorico.rocks', '12345678');
        $this->listingContext->iDoASearchOnTheHomePage();
        $this->listingContext->iFindAListing($listingTitle);
        $this->pageContext->iClickOnTheElementWithCSSSelector("a.contact-opener");
        $this->pageContext->iWait(2000);
        $this->fillField("message_body", $message);
        $this->pressButton("Send");
        $this->pageContext->iShouldBeOnThePageNamed("dashboard_message_thread_view");
        $this->pageContext->iWait(2000);
        $this->assertSession()->elementTextContains('css', "div.posts-holder div.post-content", $message);
    }


}

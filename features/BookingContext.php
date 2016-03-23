<?php
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Cocorico\CoreBundle\Entity\BookingBankWire;

class BookingContext extends CommonContext
{
    /**
     * @Then /^I should be on this new booking page:$/
     *
     * @param TableNode $fields
     */
    public function iShouldBeOnThisNewBookingPage(TableNode $fields)
    {
        $newBookingUrl = $this->generateNewBookingPageUrl($fields);

        if ($newBookingUrl) {
            $this->assertSession()->addressEquals($newBookingUrl);
            try {
                $this->assertStatusCodeEquals(200);
            } catch (UnsupportedDriverActionException $e) {
            }
        } else {
            PHPUnit_Framework_TestCase::assertNotFalse($newBookingUrl, 'I am not on the new booking page');
        }
    }


    /**
     * @Given /^I am on this new booking page:"$/
     *
     * @param TableNode $fields
     */
    public function iAmOnThisNewBookingPage(TableNode $fields)
    {
        $newBookingUrl = $this->generateNewBookingPageUrl($fields);

        if ($newBookingUrl) {
            $this->getSession()->visit($newBookingUrl);
        } else {
            PHPUnit_Framework_TestCase::assertNotFalse($newBookingUrl, 'I am not on the new booking page');
        }
    }

    /**
     * @param TableNode $fields
     * @return bool|string
     */
    public function generateNewBookingPageUrl(TableNode $fields)
    {
        $parameters = $fields->getRowsHash();
        $listing = $this->listingContext->getListingByTitle($parameters["listingTitle"]);
        if (null !== $listing) {
            $start = new \DateTime();
            $start->add(new DateInterval('P' . $parameters["start"] . 'D'));
            $end = new \DateTime();
            $end->add(new DateInterval('P' . $parameters["end"] . 'D'));

            return $this->generatePageUrl(
                "booking new",
                array(
                    "listing_id" => $listing->getId(),
                    "start" => $start->format('Y-m-d'),
                    "end" => $end->format('Y-m-d'),
                    "start_time" => $parameters["start_time"],
                    "end_time" => $parameters["end_time"],
                )
            );
        }

        return false;
    }


    /**
     * Same than scenario "As an asker i can find a listing so that i can make a booking request"
     *
     * @Given /^(?:|I|An asker) make this new booking request:$/
     *
     * @var TableNode $fields
     */
    public function iMakeANewBookingRequest(TableNode $fields)
    {
        $parameters = $fields->getRowsHash();

        $askerEmail = $parameters["askerEmail"];
        $askerPassword = $parameters["askerPassword"];
        $offererEmail = $parameters["offererEmail"];
        $amountExpected = $this->fixStepArgument($parameters["amountExpected"]);
        $feesExpected = $this->fixStepArgument($parameters["feesExpected"]);
        $totalAmountExpected = $this->fixStepArgument($parameters["totalAmountExpected"]);

        $this->userContext->iAmLoggedInUser($askerEmail, $askerPassword);
        $this->iAmOnThisNewBookingPage($fields);
        //Amounts verifications
        $this->assertSession()->elementTextContains('css', "[data-id=booking-amount]", $amountExpected);
        $this->assertSession()->elementTextContains('css', "[data-id=booking-fees]", $feesExpected);
        $this->assertSession()->elementTextContains('css', "[data-id=booking-total]", $totalAmountExpected);

        //Fill booking request informations
        $this->fillField("message", "Hello, I want to book your listing");
        $this->iFillCreditCardInformations(1);
        $this->pageContext->checkHidden("I accept the terms of use");
        $this->pressButton("Validate booking");

        $this->retryNewBookingWhenCardFailed();

        $this->assertSession()->addressEquals("https://3ds-acs.test.modirum.com/mdpayacs/pareq");
        $this->fillField("password", "secret3");
        $this->pressButton("Submit");
        $this->pageContext->iShouldBeOnThePageNamed("dashboard_booking_show_asker");
        $this->assertSession()->elementTextContains('css', ".post [data-id=booking-total]", $totalAmountExpected);
        $this->userContext->iShouldGetAnEmail("booking_request_asker", $askerEmail);
        $this->userContext->iShouldGetAnEmail("booking_request_offerer", $offererEmail);
        $this->assertSession()->pageTextContains("SUCCESS! You have booked this listing successfully");
    }

    /**
     * Retry new booking with an other card when first bank card number failed
     *
     * @When /^I eventually retry new booking request if first card failed$/
     *
     */
    public function retryNewBookingWhenCardFailed()
    {
        //Mangopay Card has been invalidated
        if ($this->getSession()->getCurrentUrl() != "https://3ds-acs.test.modirum.com/mdpayacs/pareq") {
            //We try to test with the second one
            if ($this->pageContext->getRouteNameFromUrl() == "booking_new") {
                $this->fillField("message", "Hello, I want to book your listing");
                $this->iFillCreditCardInformations(2);
                $this->pageContext->checkHidden("I accept the terms of use");
                $this->pressButton("Validate booking");
                $this->pageContext->iWait(2000);
            }
        }
    }

    /**
     * @When /^I fill correctly credit card informations$/
     *
     * @param int $card first or second card display on mangopay page
     */
    public function iFillCreditCardInformations($card = 1)
    {
        $link = $this->getSession()->getPage()->findLink("Test card");
        $this->visitPath($link->getAttribute("href"));
        $this->pageContext->iWait(2000);

        //Get Test cards number from mangopay
        $linkTab = $this->getSession()->getPage()->findLink("Visa/MasterCard");
        $linkTab = $linkTab->getAttribute("href");
        $panel = $this->getSession()->getPage()->find('css', $linkTab);
        $panel = $panel->getText();
        preg_match_all('#(\d{15,})#', $panel, $cardNumbers);
        $cardNumber = $cardNumbers[0][$card - 1];

        $this->getSession()->back();
        $this->pageContext->iWaitForJqueryLoading(5000);
        $this->fillField("cardNumber", trim($cardNumber));
        $this->fillField("cardExpirationMonth", "05");
        $this->fillField("cardExpirationYear", date('y', strtotime('+1 year')));
        $this->fillField("cardCvx", "123");
    }

    /**
     * Same than scenario "As an offerer i can accept a booking request"
     *
     * @Given /^I accept this new booking request:$/
     * @Given /^An offerer accept my booking request:$/
     *
     * @var TableNode $fields
     */
    public function iAcceptANewBookingRequest(TableNode $fields)
    {
        $parameters = $fields->getRowsHash();
        $offererEmail = $parameters["offererEmail"];
        $offererPassword = $parameters["offererPassword"];
        $askerEmail = $parameters["askerEmail"];

        $totalAmountExpected = $this->fixStepArgument($parameters["totalAmountExpected"]);

        $this->userContext->iAmLoggedInUser($offererEmail, $offererPassword);
        $this->pageContext->clickOnDashboardUserTypeSubMenu("Bookings", "offerer");
        $this->pageContext->iShouldBeOnThePage("dashboard booking offerer");
        $this->assertSession()->pageTextContains("1 result");
        $this->pageContext->clickLink("Show");

        $this->pageContext->iShouldBeOnThePageNamed("dashboard_booking_show_offerer");
        $this->assertSession()->elementTextContains('css', ".post [data-id=booking-total]", $totalAmountExpected);

        $this->pageContext->clickLink("Accept");
        $this->pageContext->iWait(3000);
        $this->assertSession()->elementTextContains('css', ".modal-body [data-id=booking-total]", $totalAmountExpected);

        $this->fillField("booking_message", "Ok let's go!");
        $this->pageContext->checkHidden("I accept the terms of use");
        $this->pageContext->iClickOnTheElementWithCSSSelector("#booking-edit-submit");
        $this->pageContext->iWait(20000);

        $this->assertSession()->pageTextContains("SUCCESS!");
        $this->userContext->iShouldGetAnEmail("booking_accepted_offerer", $offererEmail);
        $this->userContext->iShouldGetAnEmail("booking_accepted_asker", $askerEmail);
    }


    /**
     * @When /^Admin make a bank wire on offerer bank account$/
     *
     * @var TableNode $fields
     */
    public function AdminMakeABankWire()
    {
        //Access to bank wire in admin
        $this->pageContext->clickOnTheElementWithXpathSelector("//*[contains(text(),'Bookings')]");
        $this->pageContext->clickOnTheElementWithXpathSelector("//*[contains(text(),'Bank Wires')]");
        $this->pageContext->iShouldBeOnThePageNamed("admin_cocorico_core_bookingbankwire_list");

        $this->pageContext->iClickOnTheElementWithCSSSelector("a.sonata-link-identifier");
        $this->pageContext->iShouldBeOnThePageNamed("admin_cocorico_core_bookingbankwire_edit");

        //Get booking bank wires informations
        $mpTransferId = $this->getSession()->getPage()->find('xpath', "//input[contains(@id,'_mangopayTransferId')]");
        $mpTransferId = $mpTransferId->getValue();

        $mpUserId = $this->getSession()->getPage()->find('xpath', "//input[contains(@id,'_user__mangopayId')]");
        $mpUserId = $mpUserId->getValue();

        $mpAmountDecimal = $this->getSession()->getPage()->find('xpath', "//input[contains(@id,'_amountDecimal')]");
        $mpAmountDecimal = $mpAmountDecimal->getValue();

        $mpWalletId = $this->getSession()->getPage()->find('xpath', "//input[contains(@id,'_user__mangopayWalletId')]");
        $mpWalletId = $mpWalletId->getValue();

        $mpBankAccountId = $this->getSession()->getPage()->find(
            'xpath',
            "//input[contains(@id,'_user__mangopayBankAccountId')]"
        );
        $mpBankAccountId = $mpBankAccountId->getValue();

//        $this->getSession()->back();
//        $this->pageContext->iWaitForJqueryLoading(5000);
//        $this->pageContext->iClickOnTheElementWithCSSSelector("a#pay-mangopay");
//        $this->pageContext->iShouldBeOnTheUrl("https://api.sandbox.mangopay.com/authorize?response_type=code&client_id=mangoapps&redirect_uri=https://dashboard.sandbox.mangopay.com/Authorize/SignIn");

        //Mangopay Dashboard access
        $this->visitPath("https://dashboard.sandbox.mangopay.com");
        $this->fillField("PartnerId", $this->getContainer()->getParameter("cocorico_mangopay.client_id"));
        $this->fillField("Email", $this->getContainer()->getParameter("cocorico_mangopay.dashboard_email"));
        $this->fillField("Password", $this->getContainer()->getParameter("cocorico_mangopay.dashboard_password"));
        $this->pageContext->iPressButtonWithCSSSelector("button.btn-sm");
        $this->pageContext->iShouldBeOnTheUrl($this->getContainer()->getParameter("cocorico_mangopay.dashboard_url"));
        $this->visitPath($this->getContainer()->getParameter("cocorico_mangopay.dashboard_operations_payout_url"));

        //Bank wire
        $this->fillField("Tag", $mpTransferId);
        $this->fillField("AuthorId", $mpUserId);
        $this->fillField("DebitedFundsAmount", $mpAmountDecimal);
        $this->fillField("FeesAmount", 0);
        $this->fillField("DebitedWalletId", $mpWalletId);
        $this->fillField("BankAccountId", $mpBankAccountId);

        //Confirm
        $this->getSession()->executeScript('window.confirm = function() { return true; }');
        $this->pageContext->iPressButtonWithCSSSelector("button.btn.btn-primary");
        $this->pageContext->iWait(5000);
        //$this->assertSession()->pageTextContains("has been successfully updated.");

        //Change bank wire status to done in admin
        $this->pageContext->iAmOnThePage("admin_cocorico_core_bookingbankwire_list");
        $this->pageContext->iClickOnTheElementWithCSSSelector("a.sonata-link-identifier");
        $statusField = $this->getSession()->getPage()->find(
            'xpath',
            "//select[contains(@id,'_status')]"
        );

        $statusField->setValue(BookingBankWire::STATUS_DONE);
        $this->pageContext->iPressButtonWithCSSSelector("button[name=btn_update_and_edit]");
        $this->pageContext->iShouldBeOnThePageNamed("admin_cocorico_core_bookingbankwire_edit");
    }


}

<?php
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Cocorico\CoreBundle\Document\ListingAvailability;

class ListingContext extends CommonContext
{
    /**
     *
     * @Then /^I should be on the "(.+)" page which "listing title" equal to "(?P<title>[^"]+)"$/
     *
     * @param string $page
     * @param string $title title
     */
    public function iShouldBeOnTheListingPage($page, $title)
    {
        $url = $this->generateListingPageUrl($page, $title);
        if ($url) {
            $this->assertSession()->addressEquals($url);
            try {
                $this->assertStatusCodeEquals(200);
            } catch (UnsupportedDriverActionException $e) {
            }
        } else {
            PHPUnit_Framework_TestCase::assertNotFalse(
                $url,
                'I am not on the listing page'
            );
        }
    }

    /**
     * @Given /^I am on the "(.+)" page which "listing title" equal to "(?P<title>[^"]+)"$/
     *
     * @param string $page
     * @param string $title title
     */
    public function iAmOnTheListingPage($page, $title)
    {
        $url = $this->generateListingPageUrl($page, $title);

        if ($url) {
            $this->getSession()->visit($url);
        } else {
            PHPUnit_Framework_TestCase::assertNotFalse($url, 'I am not the listing page');
        }
    }

    /**
     * @param $page
     * @param $title
     * @return bool|string
     */
    public function generateListingPageUrl($page, $title)
    {
        $listing = $this->getListingByTitle($title);
        if (null !== $listing) {
            return $this->generatePageUrl(
                $page,
                array(
                    "id" => $listing->getId(),
                    "listing_id" => $listing->getId(),
                    "slug" => $listing->getSlug()
                )
            );
        }

        return false;
    }


    /**
     * @param $title
     * @return \Cocorico\CoreBundle\Entity\Listing|null
     */
    public function getListingByTitle($title)
    {
        $em = $this->getEntityManager();

        /** @var $entity \Cocorico\CoreBundle\Entity\Listing */
        $entity = $em->getRepository('CocoricoCoreBundle:Listing')->findOneByTitle(
            $title,
            $this->getContainer()->getParameter('cocorico.locale')
        );

        return $entity;
    }


    /**
     * @Given /^I find a listing which "listing title" equal to "(?P<title>[^"]+)"$/
     *
     * @param string $title title
     */
    public function iFindAListing($title)
    {
        $this->pageContext->iShouldBeOnThePage("listing search result");
        $this->assertSession()->pageTextContains("1 results");
        $this->pageContext->iClickOnTheElementWithCSSSelector("article.listing-post a.listing-box");
        $this->iShouldBeOnTheListingPage("listing show", $title);
    }

    /**
     * @Given /^there are following availabilities:$/
     *
     * @param $table TableNode
     */
    public function thereAreFollowingAvailability(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $entity = $this->getListingByTitle($data['listingTitle']);
            if ($entity) {
                $this->setListingAvailability(
                    $entity->getId(),
                    $data['day'],
                    $data['status'],
                    $data['price']
                );
            }
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param        $listingId
     * @param  int   $day    number of day after today
     * @param  int   $status ListingAvailability
     * @param  int   $price
     * @return \Cocorico\CoreBundle\Document\ListingAvailability
     */
    public function setListingAvailability(
        $listingId,
        $day,
        $status,
        $price
    ) {
        //Availability
        $now = new \DateTime(date('Y-m-d'));
        $now->add(new \DateInterval('P' . $day . 'D'));
        $availability = new ListingAvailability();
        $availability->setDay($now);
        $availability->setStatus($status);
        $availability->setListingId($listingId);
        $availability->setPrice($price);
        $this->getDocumentManager()->persist($availability);
        $this->getDocumentManager()->flush();


        return $availability;
    }

    /**
     * @Given /^I do a search on the home page$/
     *
     */
    public function iDoASearchOnTheHomePage()
    {
        $this->pageContext->iAmOnThePage('home');
        $this->iSelectCategories("Category1_1");
        $this->fillField('location', 'Paris, France');
        $this->pressButton('Search');
        $this->pageContext->iWait('1000');
    }

    /**
     * @When /^I select category "([^"]*)"$/
     *
     * @param $category
     */
    public function iSelectCategory($category)
    {
        $this->pageContext->iWaitForJqueryLoading(1000);
        $this->pageContext->clickOnTheElementWithXpathSelector("//span[text()='" . $category . "']");
    }

    /**
     * @When /^I select categories "([^"]*)"$/
     *
     * @param $category
     */
    public function iSelectCategories($category)
    {
        $this->pageContext->iWaitForJqueryLoading(1000);
        $categoryIdJS = "$('select#categories > option:contains(\"" . $category . "\")').val()";
        $javascript = "$('#categories').multiselect('select'," . $categoryIdJS . ");";
        $this->getSession()->executeScript($javascript);
    }


    /**
     * @When /^I select a characteristic "(.+)" with value "(.+)"$/
     *
     * @param $characteristic
     * @param $value
     */
    public function iSelectACharacteristic($characteristic, $value)
    {
        $this->pageContext->clickOnTheElementWithXpathSelector("//span[text()='" . $characteristic . "']");
        $this->pageContext->clickOnTheElementWithXpathSelector("//span[text()='" . $value . "']");
    }

}

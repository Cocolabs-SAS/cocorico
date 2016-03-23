<?php
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\UnsupportedDriverActionException;

class PageContext extends CommonContext
{
    /**
     * @When /^I follow the (.+) link in the "(?P<typeEmail>[^"]+)" mail send to "(?P<email>[^"]+)"$/
     *
     * @param $routeName
     * @param $typeEmail
     * @param $email
     * @return bool
     */
    public function iFollowALinkInAnEmail($routeName, $typeEmail, $email)
    {
        $emailContent = $this->getEmailContent($typeEmail, $email);
        $links = $this->getUrls($emailContent);
        $router = $this->getContainer()->get('router');
        $routeName = str_replace(' ', '_', trim($routeName));

        //Verify if the link is the expected one
        foreach ($links as $i => $link) {
//            echo "link: $link" . "\n";;
            $linkPart = trim(parse_url($link, PHP_URL_PATH));
//            echo "linkPart: $linkPart" . "\n";
            try {
                $routeInfo = $router->match($linkPart);
//                echo "routeInfo:" . $routeInfo["_route"] . "\n";
                if (in_array(
                    $routeInfo["_route"],
                    array($routeName, 'cocorico_core_' . $routeName, 'cocorico_user_' . $routeName)
                )
                ) {
//                    echo "OKKK $link" . "\n";
                    $this->getSession()->visit(trim($link));

                    return true;
                }
            } catch (Exception $e) {
            }
        }

        return false;
    }


    /**
     * @Then /^I should be on the "(.+)" page$/
     *
     * @param $page mixed
     */
    public function iShouldBeOnThePage($page)
    {
        $this->assertSession()->addressEquals($this->generatePageUrl($page));

        try {
            $this->assertStatusCodeEquals(200);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @Then /^I should be on "(.+)" url$/
     *
     * @param $url mixed
     */
    public function iShouldBeOnTheUrl($url)
    {
        $this->assertSession()->addressEquals($url);

        try {
            $this->assertStatusCodeEquals(200);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @Then /^I should be on the page which route name is "(.+)"$/
     *
     * @param string $routeName
     */
    public function iShouldBeOnThePageNamed($routeName)
    {
        $route = $this->getRouteNameFromUrl();

        PHPUnit_Framework_TestCase::assertEquals(
            $route,
            $routeName,
            sprintf('Page don\'t correspond to this route name "%s"', $routeName)
        );
    }

    /**
     *
     * @return array|false|mixed
     */
    public function getRouteNameFromUrl()
    {
        $baseUrl = $this->getMinkParameter("base_url");
        $url = $this->getSession()->getCurrentUrl();
        $url = "/" . ltrim(str_replace($baseUrl, "", $url), "/");
        $route = $this->getContainer()->get('router')->match("$url");

        if (substr($route["_route"], 0, 14) != "admin_cocorico") {
            $route = str_replace("cocorico_", "", $route["_route"]);
        } else {
            $route = $route["_route"];
        }

        return $route;
    }

    /**
     * @Given /^I am on the (.+) (page)$/
     *
     * @param $page mixed
     */
    public function iAmOnThePage($page)
    {
        $this->getSession()->visit($this->generatePageUrl($page));
        $this->iWait(1000);
    }


    /**
     * @Then /^I wait (?P<time>\d+) ms$/
     *
     * @param $time
     */
    public function iWait($time)
    {
        $this->getSession()->wait($time);
    }

    /**
     * @Then /^I wait (?P<time>\d+) ms for Jquery loading$/
     *
     * @param $time
     * @param $jqueryLoaded
     */
    public function iWaitForJqueryLoading($time, $jqueryLoaded = true)
    {
        if ($jqueryLoaded) {
            $this->getSession()->wait($time, 'typeof window.jQuery == "function"');
        } else {
            $this->getSession()->wait($time);
        }
    }

    /**
     * Click on the element with the provided css selector
     *
     * @When /^(?:|I )press button with css "([^"]*)"$/
     *
     * @param $cssSelector
     */
    public function iPressButtonWithCSSSelector($cssSelector)
    {
        $session = $this->getSession(); // get the mink session
        $element = $session->getPage()->find(
            'css',
            $cssSelector
        );

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate css selector: "%s"', $cssSelector));
        }

        $element->press();
    }

    /**
     * Click on the element with the provided CSS Selector
     *
     * @When /^I click on the element with css selector "([^"]*)"$/
     *
     * @param $cssSelector
     */
    public function iClickOnTheElementWithCSSSelector($cssSelector)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'css',
            $cssSelector
        );
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS Selector: "%s"', $cssSelector));
        }

        $element->click();
    }


    /**
     * Click on the element with the provided xPath Selector
     *
     * @When /^I click on the element with xpath selector "([^"]*)"$/
     *
     * @param $xPathSelector
     */
    public function clickOnTheElementWithXpathSelector($xPathSelector)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $xPathSelector
        );
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate xPath Selector: "%s"', $xPathSelector));
        }

        $element->click();
    }


    /**
     * @Then /^I should be on the "(.+)" (page|step) with "(?P<name>[^"]+)" equal to "(?P<value>[^"]+)"$/
     * @Then /^I should be redirected to the "(.+)" (page|step) with "(?P<name>[^"]+)" equal to "(?P<value>[^"]+)"$/
     * @Then /^I should still be on the "(.+)" (page|step) with "(?P<name>[^"]+)" equal to "(?P<value>[^"]+)"$/
     *
     * @param string $page
     * @param string $name  route param name
     * @param string $value route param value
     */
//    public function iShouldBeOnThePageOf($page, $name, $value)
//    {
//        $routeParams = array($name => $value);
////        PHPUnit_Framework_TestCase::fail(print_r($routeParams, 1));
//        $this->assertSession()->addressEquals($this->generatePageUrl($page, $routeParams));
//
//        try {
//            $this->assertStatusCodeEquals(200);
//        } catch (UnsupportedDriverActionException $e) {
//        }
//    }


    /**
     * Fills in form date fields with provided table. Values are number of days from today.
     *
     * @When /^(?:|I )fill date range with the following:$/
     *
     * @param TableNode $fields
     */
    public function fillDateRangeFields(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $date = new \DateTime();
            $date->add(new DateInterval('P' . $value . 'D'));

            $dateJs = $date->format("Y") . "," . ($date->format("n") - 1) . "," . $date->format("d");
            $javascript = "$('#" . $field . "').datepicker('setDate', new Date(" . $dateJs . "));";
            $this->getSession()->executeScript($javascript);
            //$this->fillField($field, $date->format('d/m/Y'));
        }
        //trigger datepicker onSelect event
        $javascript = "$('.ui-datepicker-current-day').click();";
        $this->getSession()->executeScript($javascript);
    }


    /**
     * @When /^I select hidden "(.+)" with value "(.+)"$/
     *
     * @param $cssSelector
     * @param $value
     */
    public function selectHidden($cssSelector, $value)
    {
        $this->iClickOnTheElementWithCSSSelector("#$cssSelector+span");
        $this->clickOnTheElementWithXpathSelector("//span[text()='" . $value . "']");
    }


    /**
     * Checks agreement checkbox with specified id|name|label|value.
     *
     * @When /^(?:|I )check hidden "(.+)"$/
     * @param String $value
     */
    public function checkHidden($value)
    {
        $this->clickOnTheElementWithXpathSelector("//label[contains(.,'$value')]");
    }

    /**
     * @When /^I drag range slider "(.+)" with min equal to "(.+)" and max equal to "(.+)"$/
     *
     * @param $cssSelector
     * @param $min
     * @param $max
     */
    public function dragRangeSlider($cssSelector, $min, $max)
    {
        //.range-box .ui-slider
        $javascript = '$("' . $cssSelector . '").slider("values", [' . $min . ', ' . $max . ']);';
        $this->getSession()->executeScript($javascript);
    }

    /**
     * Click on jquery tab
     *
     * @When /^(?:|I )click on "(.+)" tab$/
     *
     * @param $tab
     */
    public function iClickOnTab($tab)
    {
        $this->clickOnTheElementWithXpathSelector("//a[contains(@href,'" . $tab . "')]");
    }

    /**
     * @When /^(?:|I )click on dashboard menu "(.+)" of "(.+)"$/
     *
     * @param $menu
     * @param $userType
     */
    public function clickOnDashboardUserTypeSubMenu($menu, $userType)
    {
        $this->iWait(2000);
        $javascript = '$("#dashboard-dropdown").addClass("open");';
        $this->getSession()->executeScript($javascript);
        $javascript = '$("a[data-id=' . $userType . ']").click();';
        $this->getSession()->executeScript($javascript);
        $this->clickLink("$userType$menu");
        //$this->clickOnTheElementWithXpathSelector('//*[@id="dashboard-dropdown"]//li[@data-id="' . $userType . '"]/ul//a[text()=\'' . $menu . '\']');
    }

    /**
     * @When /^(?:|I) click on dashboard menu of "(.+)"$/
     *
     * @param $userType
     */
    public function clickOnDashboardUserTypeMenu($userType)
    {
        $this->iWait(2000);
        $javascript = '$("#dashboard-dropdown").addClass("open");';
        $this->getSession()->executeScript($javascript);
        $javascript = '$("a[data-id=' . $userType . ']").click();';
        $this->getSession()->executeScript($javascript);
    }

    /**
     * @When /^(?:|I )click on dashboard menu$/
     *
     */
    public function clickOnDashboardMenu()
    {
        $this->iWait(2000);
        $javascript = '$("#dashboard-dropdown").addClass("open");';
        $this->getSession()->executeScript($javascript);
    }
}

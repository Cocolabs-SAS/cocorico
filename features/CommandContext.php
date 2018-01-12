<?php

use Cocorico\CoreBundle\Command\AlertExpiringBookingsCommand;
use Cocorico\CoreBundle\Command\AlertImminentBookingsCommand;
use Cocorico\CoreBundle\Command\AlertListingsCalendarUpdateCommand;
use Cocorico\CoreBundle\Command\CheckBookingsBankWiresCommand;
use Cocorico\CoreBundle\Command\CurrencyCommand;
use Cocorico\CoreBundle\Command\ExpireBookingsCommand;
use Cocorico\CoreBundle\Command\ValidateBookingsCommand;
use Cocorico\ListingSearchBundle\Command\ComputeListingsNotationCommand;
use Lexik\Bundle\CurrencyBundle\Command\ImportCurrencyCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandContext extends CommonContext
{
    /** @var  CommandTester $tester */
    protected $tester;


    /**
     * @When /^(?:|I|System user) run "([^"]*)" command$/
     *
     * @param string $name
     */
    public function iRunCommand($name)
    {
        $application = new Application($this->kernel);
        $arguments = array();

        switch ($name) {
            case "cocorico:currency:update":
                $application->add(new CurrencyCommand());
                $application->add(new ImportCurrencyCommand());
                break;
            case "cocorico:bookings:expire":
                $application->add(new ExpireBookingsCommand());
                $arguments["--expiration-delay"] = 0;//cocorico.booking.expiration_delay
                $arguments["--acceptation-delay"] = 0;//cocorico.booking.acceptation_delay
                $arguments["--test"] = true;
                break;
            case "cocorico:bookings:alertExpiring":
                $application->add(new AlertExpiringBookingsCommand());
                $arguments["--alert-delay"] = 0;//cocorico.booking.alert_expiration_delay
                $arguments["--expiration-delay"] = 0;//cocorico.booking.expiration_delay
                $arguments["--acceptation-delay"] = 0;//cocorico.booking.acceptation_delay
                $arguments["--test"] = true;
                break;
            case "cocorico:bookings:alertImminent":
                $application->add(new AlertImminentBookingsCommand());
                $arguments["--delay"] = 1440;//cocorico.booking.imminent_delay
                $arguments["--test"] = true;
                break;
            case "cocorico:bookings:validate":
                $application->add(new ValidateBookingsCommand());
                $arguments["--delay"] = -1440;//cocorico.booking.validated_delay
                $arguments["--moment"] = 'start';//cocorico.booking.validated_moment
                $arguments["--test"] = true;
                break;
            case "cocorico:bookings:checkBankWires":
                $application->add(new CheckBookingsBankWiresCommand());
                break;
            case "cocorico:listings:alertUpdateCalendars":
                $application->add(new AlertListingsCalendarUpdateCommand());
                break;
            case "cocorico_listing_search:computeNotation":
                $application->add(new ComputeListingsNotationCommand());
                break;
            default:
                echo "Command not found";
        }

        $command = $application->find($name);
        $arguments["command"] = $command->getName();

        $this->tester = new CommandTester($command);
        $this->tester->execute($arguments);
    }


    /**
     * @Then /^(?:|I|He) should see "([^"]*)" on console$/
     *
     * @param string $string
     */
    public function iShouldSee($string)
    {
        PHPUnit_Framework_TestCase::AssertEquals(
            trim($string),
            trim($this->tester->getDisplay()),
            sprintf("Command return unexpected result \"%s\"", $this->tester->getDisplay())
        );
    }

    /**
     * @Then /^I should see "([^"]*)" on console messages$/
     *
     * @param string $string
     */
    public function iShouldSeeInMessages($string)
    {
        PHPUnit_Framework_TestCase::assertRegExp(
            "/$string/",
            trim($this->tester->getDisplay()),
            sprintf("Command return unexpected result \"%s\"", $this->tester->getDisplay())
        );
    }

}
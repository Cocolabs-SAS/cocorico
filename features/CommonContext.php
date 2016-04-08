<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class CommonContext extends RawMinkContext implements Context, KernelAwareContext
{

    /**
     * Faker. (for fake data generation)
     *
     * @var Generator
     */
    protected $faker;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /** @var  PageContext */
    protected $pageContext;

    /** @var  ListingContext */
    protected $listingContext;

    /** @var  UserContext */
    protected $userContext;

    /**
     * Actions.
     *
     * @var array
     */
    protected $actions = array(
        'viewing' => 'show',
        'creation' => 'create',
        'editing' => 'update',
        'building' => 'build',
    );

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    /**
     * Get entity manager.
     *
     * @return ObjectManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine')->getManager();
    }

    /**
     * Get document manager.
     *
     * @return ObjectManager
     */
    protected function getDocumentManager()
    {
        return $this->getService('doctrine_mongodb')->getManager();
    }

    /**
     * Returns Container instance.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Get service by id.
     *
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * Get repository by resource name.
     *
     * @param string $resource
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($resource)
    {
        return $this->getService('cocorico.repository.' . $resource);
    }

    /**
     * Get current user instance.
     *
     * @return null|UserInterface
     *
     * @throws \Exception
     */
    protected function getUser()
    {
        $token = $this->getSecurityTokenStorage()->getToken();

        if (null === $token) {
            throw new \Exception('No token found in security context.');
        }

        return $token->getUser();
    }

    /**
     * Get security context.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    protected function getSecurityTokenStorage()
    {
        return $this->getContainer()->get('security.token_storage');
    }

    /**
     * Get translator.
     *
     * @return \Symfony\Component\Translation\Translator
     */
    protected function getTranslator()
    {
        return $this->getContainer()->get('translator');
    }

    /**
     * @return mixed
     */
    protected function getSpoolDir()
    {
        return $this->getContainer()->getParameter('swiftmailer.spool.default.file.path');
    }

    protected function getTestLogDir()
    {
        return $this->kernel->getRootDir() . '/../web/test/behat/';
    }

    /**
     * Get urls from string
     *
     * @param $string
     * @return mixed
     */
    protected function getUrls($string)
    {
        $regex = '/https?\:\/\/[^\" ]+/i';
        preg_match_all($regex, $string, $matches);

        return ($matches[0]);
    }


    /**
     * Presses button with specified id|name|title|alt|value.
     *
     * @var string $button
     */
    protected function pressButton($button)
    {
        $this->getSession()->getPage()->pressButton($this->fixStepArgument($button));
    }

    /**
     * Clicks link with specified id|title|alt|text.
     *
     * @var string $link
     */
    protected function  clickLink($link)
    {
        $this->getSession()->getPage()->clickLink($this->fixStepArgument($link));
    }

    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @var string $field
     * @var string $value
     */
    protected function fillField($field, $value)
    {
        $this->getSession()->getPage()->fillField($this->fixStepArgument($field), $this->fixStepArgument($value));
    }

    /**
     * Selects option in select field with specified id|name|label|value.
     *
     * @var string $select
     * @var string $option
     */
    public function selectOption($select, $option)
    {
        $this->getSession()->getPage()->selectFieldOption(
            $this->fixStepArgument($select),
            $this->fixStepArgument($option)
        );
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }


    /**
     * Generate page url.
     * This method uses simple convention where page argument is prefixed
     * with "cocorico_" and used as route name passed to router generate method.
     *
     * @param object|string $page
     * @param array         $parameters
     *
     * @return string
     */
    protected function generatePageUrl($page, array $parameters = array())
    {
        if (is_object($page)) {
            return $this->generateUrl($page, $parameters);
        }

        $route = str_replace(' ', '_', trim($page));
        /** @var  $routes \Symfony\Component\Routing\RouteCollection */
        $routes = $this->getContainer()->get('router')->getRouteCollection();

        if (null === $routes->get($route)) {
            $route = 'cocorico_' . $route;
            $locale = $this->getContainer()->getParameter('cocorico.locale');
            $route = $locale . \JMS\I18nRoutingBundle\Router\I18nLoader::ROUTING_PREFIX . $route;
            //echo($route);
        }

        if (null === $routes->get($route)) {
            $route = str_replace('cocorico_', 'cocorico_user_', $route);
        }

        $route = str_replace(array_keys($this->actions), array_values($this->actions), $route);
        $route = str_replace(' ', '_', $route);

        return $this->generateUrl($route, $parameters);
    }

    /**
     * Generate url.
     *
     * @param string  $route
     * @param array   $parameters
     * @param Boolean $absolute
     *
     * @return string
     */
    protected function generateUrl($route, array $parameters = array(), $absolute = false)
    {
        return $this->locatePath($this->getContainer()->get('router')->generate($route, $parameters, $absolute));
    }


    /**
     * @param $typeEmail
     * @param $email
     * @return string
     */
    protected function getEmailContent($typeEmail, $email)
    {
        $spoolDir = $this->getSpoolDir();
        $filesystem = new Filesystem();

        if ($filesystem->exists($spoolDir)) {
            $finder = new Finder();

            // find every files inside the spool dir except hidden files
            $finder
                ->in($spoolDir)
                ->ignoreDotFiles(true)
                ->files();

            foreach ($finder as $file) {
                /** @var $message SplFileInfo */
                $message = unserialize(file_get_contents($file));

                // check the recipients
                $recipients = array_keys($message->getTo());
                if (!in_array($email, $recipients)) {
                    continue;
                }

                $body = $message->getBody();
                if (strpos($body, "<!--" . $typeEmail . "-->") !== false) {
                    return $body;
                }


            }
        }

        return "";
    }


    public function attachFileToField($field, $path)
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $this->getSession()->getPage()->attachFileToField($field, $path);
    }

    /**
     * Assert that given code equals the current one.
     *
     * @param integer $code
     */
    protected function assertStatusCodeEquals($code)
    {
        $this->assertSession()->statusCodeEquals($code);

    }

    /**
     * @BeforeScenario
     *
     * @param $scope BeforeScenarioScope
     *
     */
    public function purgeMongoDatabase(BeforeScenarioScope $scope)
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $documentManager */
        $documentManager = $this->getService('doctrine.odm.mongodb.document_manager');
        $documentManager->createQueryBuilder('CocoricoCoreBundle:ListingAvailability')
            ->remove()
            ->field('listingId')->exists(true)
            ->getQuery()
            ->execute();
    }

    /**
     * Purge spool dir (for emails, ...)
     *
     * @BeforeScenario
     */
    public function purgeSpool()
    {
        $spoolDir = $this->getSpoolDir();

        $filesystem = new Filesystem();

        $filesystem->remove($spoolDir);
    }

    /**
     * Purge test log dir
     *
     *
     */
//    public function purgeTestLogDir()
//    {
//        $testLogDir = $this->getTestLogDir();
//
//        $filesystem = new Filesystem();
//
//        $filesystem->remove($testLogDir);
//    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var \Behat\Behat\Context\Environment\InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        if ($environment->hasContextClass('PageContext')) {
            $this->pageContext = $environment->getContext('PageContext');
        }

        if ($environment->hasContextClass('ListingContext')) {
            $this->listingContext = $environment->getContext('ListingContext');
        }

        if ($environment->hasContextClass('UserContext')) {
            $this->userContext = $environment->getContext('UserContext');
        }
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function setUsersMangoPayIdAndWallerId(BeforeScenarioScope $scope)
    {
        $userManager = $this->getContainer()->get('cocorico_user.user_manager');
        /** @var User[] $users */
        $users = $userManager->findUsers();
        foreach ($users as $i => $user) {
            if (!$user->getMangopayId()) {

                $event = new UserEvent($user);
                $this->getContainer()->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
                $user = $event->getUser();

                $userManager->updateUser($user);
            }
        }
    }


    /**
     * AfterStep
     *
     * @param AfterStepScope $scope
     */
//    public function showFailedStepResponse(AfterStepScope $scope)
//    {
//        $testResult = $scope->getTestResult();
//
//        if ($testResult->getResultCode() != TestResult::FAILED || $testResult->isPassed()) {
//            return;
//        }
//
//        if (true !== $this->getMinkParameter('show_auto')) {
//            return;
//        }
//
//        if (null === $this->getMinkParameter('show_cmd')) {
//            return;
//        }
//
//
//        $session = $this->getSession();
//        $page = $session->getPage();
//
//        $logDir = $this->getTestLogDir();
//        $baseUrl = $this->getMinkParameter("base_url");
//
//        $stepText = str_replace(array(" ", "\"", "'", ",", "*", ":"), "_", $scope->getStep()->getText());
//        $fileName = date('YmdHis') . '_' . $stepText . '_' . uniqid();
//
//        if (!file_exists($logDir)) {
//            mkdir($logDir);
//        }
//
//        $date = date('Y-m-d H:i:s');
//        $url = $session->getCurrentUrl();
//        $html = $page->getContent();
//
//        //HTML
//        $html = "<!-- HTML dump from behat  \nDate: $date  \nUrl:  $url  -->\n " . $html;
//        $htmlCapturePath = $logDir . $fileName . '.html';
//        $urlCapturePath = $baseUrl . "test/behat/" . $fileName . ".html";
//        file_put_contents($htmlCapturePath, $html);
////        $message = "\nHTML saved to: " . $logDir . $fileName . ".html";
////        $message .= "\nHTML available at: " . $logDir . $fileName . ".html";
//        system(sprintf($this->getMinkParameter('show_cmd'), escapeshellarg($urlCapturePath)));
//
//        //IMAGE
////        $this->getSession()->getDriver()->keyPress('html', '-', 'ctrl');
////        $this->getSession()->getDriver()->keyPress('html', '-', 'ctrl');
////        $this->getSession()->getDriver()->keyPress('html', '-', 'ctrl');
////        $this->getSession()->getDriver()->keyPress('html', '-', 'ctrl');
//        $screenShot = $this->getSession()->getScreenshot();
//        $screenShotFilePath = $logDir . '/' . $fileName . '.png';
//        $urlCapturePath = $baseUrl . "test/behat/" . $fileName . ".png";
//        file_put_contents($screenShotFilePath, $screenShot);
////        $message .= "\nScreen shot saved to: " . $logDir . "/" . $fileName . ".png";
////        $message .= "\nScreen shot available at: " . $baseUrl . "test/behat/" . $fileName . ".png";
//        system(sprintf($this->getMinkParameter('show_cmd'), escapeshellarg($urlCapturePath)));
//
//    }

}


<?php

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Cocorico\UserBundle\Entity\User;

class UserContext extends CommonContext
{
    /**
     * @Given /^I am logged in as user "(?P<email>[^"]+)" with password "(?P<password>[^"]+)"$/
     * @Given /^I am logged in user$/
     * @Given /^I am logged in as user "(?P<email>[^"]+)"$/
     *
     * @param string $email
     * @param string $password
     */
    public function iAmLoggedInUser($email = 'user@cocorico.rocks', $password = '')
    {
        $this->iAmLoggedInAsRole('ROLE_USER', $email, $password);
    }

    /**
     * @param        $role
     * @param string $email
     * @param string $password
     */
    private function iAmLoggedInAsRole($role, $email = 'user@cocorico.rocks', $password = '')
    {
        if (!$password) {
            $password = $this->faker->word;
            $this->thereIsUser(
                $this->faker->firstName,
                $this->faker->lastName,
                $email,
                $password,
                $role
            );
        }

        if ($this->getSession()->getPage()->findLink("Logout") != null) {
            $this->iAmNotLoggedIn();
        }

        $this->getSession()->visit($this->generatePageUrl('login'));

        $this->fillField('Email', $email);
        $this->fillField('Password', $password);
        $this->pressButton('Login');
    }

    /**
     * @Given /^I am not logged in$/
     */
    public function iAmNotLoggedIn()
    {
        $this->getSession()->visit($this->generatePageUrl('logout'));
    }


    /**
     * @Given /^I am logged in on admin as user "(?P<email>[^"]+)" with password "(?P<password>[^"]+)"$/
     * @param string $email
     * @param string $password
     */
    public function iAmLoggedInUserOnAdmin($email, $password)
    {
        if ($this->getSession()->getPage()->findLink("Logout") != null) {
            $this->iAmNotLoggedIn();
        }

        $this->getSession()->visit($this->generatePageUrl('sonata_user_admin_security_login'));
        $this->pageContext->iShouldBeOnThePageNamed('sonata_user_admin_security_login');
        $this->pageContext->iWaitForJqueryLoading(1000);
        $this->fillField('username', $email);
        $this->fillField('password', $password);
        $this->pageContext->iPressButtonWithCSSSelector('#_submit');
    }


    /**
     * @Given /^there are following users:$/
     *
     * @param $table TableNode
     */
    public function thereAreFollowingUsers(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->thereIsUser(
                $data['first_name'] ? $data['first_name'] : $this->faker->firstName,
                $data['last_name'] ? $data['last_name'] : $this->faker->lastName,
                $data['email'],
                isset($data['password']) ? $data['password'] : $this->faker->word,
                'ROLE_USER',
                isset($data['enabled']) ? $data['enabled'] : 'yes',
                isset($data['groups']) && !empty($data['groups']) ? explode(',', $data['groups']) : array(),
                false
            );
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param        $firstName
     * @param        $lastName
     * @param        $email
     * @param        $password
     * @param null   $role
     * @param string $enabled
     * @param array  $groups
     * @param bool   $flush
     * @return User|\FOS\UserBundle\Model\UserInterface
     */
    public function thereIsUser(
        $firstName,
        $lastName,
        $email,
        $password,
        $role = null,
        $enabled = 'yes',
        $groups = array(),
        $flush = true
    ) {
        /** @var  $userManager \Cocorico\UserBundle\Model\UserManager */
        $userManager = $this->getContainer()->get('cocorico_user.user_manager');


        if (null === $user = $userManager->findUserByEmail($email)) {
            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setUsername($email);
            $user->setEmail($email);
            $user->setEnabled('yes' === $enabled);
            $user->setPlainPassword($password);

            if (null !== $role) {
                $user->addRole($role);
            }

            $this->getEntityManager()->persist($user);

//            foreach ($groups as $groupName) {
//                if ($group = $this->findOneByName('group', $groupName)) {
//                    $user->addGroup($group);
//                }
//            }

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $user;
    }


    /**
     *  Check if this is the correct email message type as been send to the correct email address
     *
     * @Then /^(?:|I|He) should receive (?:|the )"(?P<typeEmail>[^"]+)" mail on "(?P<email>[^"]+)"$/
     *
     * @param $typeEmail
     * @param $email
     */
    public function iShouldGetAnEmail($typeEmail, $email)
    {
        $emailContent = $this->getEmailContent($typeEmail, $email);

        PHPUnit_Framework_TestCase::assertNotEmpty(
            $emailContent,
            sprintf("The \"%s\" mail was not sent", $typeEmail)
        );
    }


    /**
     * @Then /^I should be on the resetting password page with token of "(?P<email>[^"]+)" user$/
     *
     * @param $email
     */
    public function iShouldBeOnThePageWithTokenOfUser($email)
    {
        /** @var  $userManager \Cocorico\UserBundle\Model\UserManager */
        $userManager = $this->getContainer()->get('cocorico_user.user_manager');
        /** @var User $user */
        $user = $userManager->findUserByEmail($email);
        $this->getEntityManager()->refresh($user);
//        echo "email:" . $email. "\n";
        if (null !== $user) {
//            echo "id:" . $user->getId(). "\n";
//            echo "emailCanonical:" . $user->getEmailCanonical(). "\n";
//            echo "confirmationToken:" . $user->getConfirmationToken() . "\n";
//            echo $this->generatePageUrl("resetting reset", array("token" => $user->getConfirmationToken())). "\n";

            $this->assertSession()->addressEquals(
                $this->generatePageUrl("resetting reset", array("token" => $user->getConfirmationToken()))
            );
        }

        try {
            $this->assertStatusCodeEquals(200);
        } catch (\Behat\Mink\Exception\UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @Then /^I should be on the "(.+)" (page|step) which "email" equal to "(?P<email>[^"]+)"$/
     * @Then /^I should be redirected to the "(.+)" (page|step) which "email" equal to "(?P<email>[^"]+)"$/
     * @Then /^I should still be on the "(.+)" (page|step) which "email" equal to "(?P<email>[^"]+)"$/
     *
     * @param string $page
     * @param string $email email
     */
    public function iShouldBeOnTheUserPage($page, $email)
    {
        //$this->getSession()->wait(3000);//Wait for entity creation

        $em = $this->getEntityManager();

        //Todo: find by email instead email
        /** @var $entity \Cocorico\UserBundle\Entity\User */
        $entity = $em->getRepository('CocoricoUserBundle:User')->findOneByEmail($email);
        if (null !== $entity) {
            $this->assertSession()->addressEquals(
                $this->generatePageUrl($page, array("id" => $entity->getId()))
            );
            try {
                $this->assertStatusCodeEquals(200);
            } catch (UnsupportedDriverActionException $e) {
            }
        } else {
            PHPUnit_Framework_TestCase::assertNotNull(
                $entity,
                sprintf('User with email "%s" not found', $email)
            );
        }
    }

}
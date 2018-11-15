<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\DataFixtures\ORM;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var  ContainerInterface container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('cocorico_user.user_manager');

        /** @var  User $user */
        $user = $userManager->createUser();
        $user->setPersonType(User::PERSON_TYPE_NATURAL);
        $user->setUsername('offerer@cocorico.rocks');
        $user->setEmail('offerer@cocorico.rocks');
        $user->setPlainPassword('12345678');
        $user->setLastName('OffererName');
        $user->setFirstName('OffererFirstName');
        $user->setCountryOfResidence('FR');
        $user->setBirthday(new \DateTime('1973-05-29'));
        $user->setEnabled(true);
        $user->setAnnualIncome(1000);
        $user->setEmailVerified(true);
        $user->setPhoneVerified(true);
        $user->setMotherTongue("en");

        $event = new UserEvent($user);
        $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        $userManager->updateUser($user);
        $this->addReference('offerer', $user);

        $user = $userManager->createUser();
        $user->setPersonType(User::PERSON_TYPE_NATURAL);
        $user->setUsername('asker@cocorico.rocks');
        $user->setEmail('asker@cocorico.rocks');
        $user->setPlainPassword('12345678');
        $user->setLastName('AskerName');
        $user->setFirstName('AskerFirstName');
        $user->setCountryOfResidence('FR');
        $user->setBirthday(new \DateTime('1975-08-27'));
        $user->setEnabled(true);
        $user->setAnnualIncome(1000);
        $user->setMotherTongue("en");

        $event = new UserEvent($user);
        $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        $userManager->updateUser($user);
        $this->addReference('asker', $user);

        $user = $userManager->createUser();
        $user->setPersonType(User::PERSON_TYPE_NATURAL);
        $user->setUsername('disableuser@cocorico.rocks');
        $user->setEmail('disableuser@cocorico.rocks');
        $user->setPlainPassword('12345678');
        $user->setLastName('DisableUserLastName');
        $user->setFirstName('DisableUserFirstName');
        $user->setCountryOfResidence('FR');
        $user->setBirthday(new \DateTime('1978-08-27'));
        $user->setEnabled(false);
        $user->setAnnualIncome(1000);
        $user->setMotherTongue("en");

        $event = new UserEvent($user);
        $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        $userManager->updateUser($user);
        $this->addReference('disable-user', $user);

        $user = $userManager->createUser();
        $user->setPersonType(User::PERSON_TYPE_NATURAL);
        $user->setLastName('super-admin');
        $user->setFirstName('super-admin');
        $user->setUsername('super-admin@cocorico.rocks');
        $user->setEmail('super-admin@cocorico.rocks');
        $user->setPlainPassword('super-admin');
        $user->setCountryOfResidence('FR');
        $user->setBirthday(new \DateTime('1978-07-01'));
        $user->setEnabled(true);
        $user->addRole('ROLE_SUPER_ADMIN');

        $event = new UserEvent($user);
        $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        $userManager->updateUser($user);
        $this->addReference('super-admin', $user);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }

}

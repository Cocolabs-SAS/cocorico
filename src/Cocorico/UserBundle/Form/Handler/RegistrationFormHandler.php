<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Form\Handler;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Cocorico\UserBundle\Mailer\MailerInterface;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Cocorico\UserBundle\Model\UserManager;
use Cocorico\UserBundle\Security\LoginManager;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RegistrationFormHandler
{
    protected $request;
    /** @var  TwigSwiftMailer */
    protected $mailer;
    /** @var  UserManager */
    protected $userManager;
    /** @var FormInterface */
    protected $form;
    protected $tokenGenerator;
    protected $loginManager;
    protected $dispatcher;

    /**
     * @param FormInterface            $form
     * @param RequestStack             $requestStack
     * @param UserManager              $userManager
     * @param MailerInterface          $mailer
     * @param TokenGeneratorInterface  $tokenGenerator
     * @param LoginManager             $loginManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        UserManager $userManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
        LoginManager $loginManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->loginManager = $loginManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param bool $confirmation
     * @return bool
     */
    public function process($confirmation = false)
    {
        /** @var User $user */
        $user = $this->createUser();
        $this->form->setData($user);

        if ('POST' === $this->request->getMethod()) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user, $confirmation);

                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     * @param bool $confirmation
     */
    protected function onSuccess(User $user, $confirmation)
    {
        $this->handleRegistration($user, $confirmation);
    }

    /**
     * @param User    $user
     * @param boolean $confirmation
     */
    public function handleRegistration(User $user, $confirmation = false)
    {
        //Set the default mother tongue for registering user
        $user->setMotherTongue($this->request->get('_locale'));

        //Eventually change user info before persist it
        $event = new UserEvent($user);
        $this->dispatcher->dispatch(UserEvents::USER_REGISTER, $event);
        $user = $event->getUser();

        if ($confirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->userManager->updateUser($user);
            $this->mailer->sendAccountCreationConfirmationMessageToUser($user);
        } else {
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            $this->loginManager->getLoginManager()->loginUser($this->loginManager->getFirewallName(), $user);
            $this->mailer->sendAccountCreatedMessageToUser($user);
        }
    }

    /**
     * @return User
     */
    protected function createUser()
    {
        return $this->userManager->createUser();
    }
}

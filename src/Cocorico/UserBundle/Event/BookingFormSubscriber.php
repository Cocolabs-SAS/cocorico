<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Cocorico\UserBundle\Event;

use Cocorico\CoreBundle\Event\BookingFormBuilderEvent;
use Cocorico\CoreBundle\Event\BookingFormEvents;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Security\LoginManager;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class BookingFormSubscriber implements EventSubscriberInterface, TranslationContainerInterface
{
    public static $credentialError = 'user.form.credential.error';

    private $loginManager;
    private $securityTokenStorage;
    private $securityAuthChecker;

    /**
     * @param TokenStorage         $securityTokenStorage
     * @param AuthorizationChecker $securityAuthChecker
     * @param LoginManager         $loginManager
     */
    public function __construct(
        TokenStorage $securityTokenStorage,
        AuthorizationChecker $securityAuthChecker,
        LoginManager $loginManager
    ) {
        $this->securityTokenStorage = $securityTokenStorage;
        $this->securityAuthChecker = $securityAuthChecker;
        $this->loginManager = $loginManager;
    }


    /**
     * Add card form fields to new booking form
     *
     * @param BookingFormBuilderEvent $event
     */
    public function onBookingNewFormBuild(BookingFormBuilderEvent $event)
    {
        $builder = $event->getFormBuilder();

        /**
         * Set the user fields according to his logging status
         *
         * @param FormInterface $form
         */
        $formUserModifier = function (FormInterface $form) {
            //Not logged
            if (!$this->securityAuthChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                $form
                    ->add(//Login form
                        'user_login',
                        'user_login',
                        array(
                            'mapped' => false,
                            /** @Ignore */
                            'label' => false
                        )
                    )->add(//Registration form
                        'user',
                        'user_registration',
                        array(
                            /** @Ignore */
                            'label' => false
                        )
                    );
            } else {//Logged
                //todo: check
                $form->remove("user");
                $form->add(
                    'user',
                    'entity_hidden',
                    array(
                        'data' => $this->securityTokenStorage->getToken()->getUser(),
                        'class' => 'Cocorico\UserBundle\Entity\User',
                        'data_class' => null
                    )
                );
            }
        };


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formUserModifier) {
                $form = $event->getForm();
                $formUserModifier($form);
            }
        );


        /**
         * Login user management
         *
         * @param FormInterface $form
         */
        $formUserLoginModifier = function (FormInterface $form) {
            if ($form->has('user_login')) {
                $userLoginData = $form->get('user_login')->getData();
                $username = $userLoginData["_username"];
                $password = $userLoginData["_password"];

                if ($username || $password) {
                    /** @var User $user */
                    $user = $this->loginManager->loginUser($username, $password);
                    if ($user) {
                        $form->getData()->setUser($user);
                        //Remove user registration and login form and add user field
                        $form->remove("user");
                        $form->remove("user_login");
                        $form->add(
                            'user',
                            'entity_hidden',
                            array(
                                'data' => $this->securityTokenStorage->getToken()->getUser(),
                                'class' => 'Cocorico\UserBundle\Entity\User',
                                'data_class' => null
                            )
                        );
                    } else {
                        $form['user_login']['_username']->addError(
                            new FormError(self::$credentialError)
                        );
                    }
                }
            }
        };


        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($formUserLoginModifier) {
                $form = $event->getForm();
                $formUserLoginModifier($form);
            }
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            BookingFormEvents::BOOKING_NEW_FORM_BUILD => array('onBookingNewFormBuild', 100),
        );
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$credentialError, 'cocorico');

        return $messages;
    }
}
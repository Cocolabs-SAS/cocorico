<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Event\BookingFormBuilderEvent;
use Cocorico\CoreBundle\Event\BookingFormEvents;
use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Security\LoginManager;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Validator\Constraints\True;

class BookingNewType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'booking.form.tac.error';
    public static $messageError = 'booking.form.message.error';
    public static $unavailableError = 'booking.new.error.unavailable';
    public static $amountError = 'booking.new.error.amount_invalid {{ min_price }}';
    public static $voucherError = 'booking.new.error.voucher';
    public static $credentialError = 'user.form.credential.error';

    private $bookingManager;
    private $loginManager;
    private $securityTokenStorage;
    private $securityAuthChecker;
    private $request;
    private $dispatcher;
    private $locale;
    private $locales;
    protected $allowSingleDay;
    protected $endDayInclude;
    protected $minStartDelay;
    protected $minStartTimeDelay;
    private $currency;
    private $currencySymbol;

    /**
     * @param BookingManager       $bookingManager
     * @param TokenStorage         $securityTokenStorage
     * @param AuthorizationChecker $securityAuthChecker
     * @param LoginManager             $loginManager
     * @param RequestStack             $requestStack
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $locales
     * @param bool                     $allowSingleDay
     * @param bool                     $endDayIncluded
     * @param int                      $minStartDelay
     * @param int                      $minStartTimeDelay
     * @param string                   $currency
     */
    public function __construct(
        BookingManager $bookingManager,
        TokenStorage $securityTokenStorage,
        AuthorizationChecker $securityAuthChecker,
        LoginManager $loginManager,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        $locales,
        $allowSingleDay,
        $endDayIncluded,
        $minStartDelay,
        $minStartTimeDelay,
        $currency
    ) {
        $this->bookingManager = $bookingManager;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->securityAuthChecker = $securityAuthChecker;
        $this->loginManager = $loginManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->locale = $this->request->getLocale();
        $this->locales = $locales;
        $this->allowSingleDay = $allowSingleDay;
        $this->endDayIncluded = $endDayIncluded;
        $this->minStartDelay = $minStartDelay;
        $this->minStartTimeDelay = $minStartTimeDelay;
        $this->currency = $currency;
        $this->currencySymbol = Intl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Booking $booking */
        $booking = $builder->getData();

        $builder
            ->add(
                'date_range',
                'date_range',
                array(
                    'mapped' => false,
                    /** @Ignore */
                    'label' => false,
                    'required' => true,
                    'start_options' => array(
                        'label' => 'booking.form.start',
                        'mapped' => true,
                        'data' => $booking->getStart()
                    ),
                    'end_options' => array(
                        'label' => 'booking.form.end',
                        'mapped' => true,
                        'data' => $booking->getEnd()
                    ),
                    'allow_single_day' => $this->allowSingleDay,
                    'end_day_included' => $this->endDayIncluded,
                    'error_bubbling' => false
                )
            )
            ->add(
                'tac',
                'checkbox',
                array(
                    'label' => 'listing.form.tac',
                    'mapped' => false,
                    'constraints' => new True(
                        array(
                            "message" => self::$tacError
                        )
                    ),
                )
            );

        if (!$this->bookingManager->getTimeUnitIsDay()) {
            //All date and time fields are hidden in this form
            $builder->add(
                'time_range',
                'time_range',
                array(
                    'mapped' => false,
                    'start_options' => array(
                        'mapped' => true,
                        'data' => $booking->getStartTime()
                    ),
                    'end_options' => array(
                        'mapped' => true,
                        'data' => $booking->getEndTime()
                    ),
                    'required' => true,
                    /** @Ignore */
                    'label' => false,
                    'block_name' => 'time_range_hidden',
                )
            );
        }

        if ($this->bookingManager->voucherIsEnabled()) {
            $builder
                ->add(
                    'voucher',
                    'voucher',
                    array(
                        'translation_domain' => 'cocorico_voucher',
                    )
                );
        }

        if ($this->bookingManager->optionIsEnabled()) {
            $builder
                ->add(
                    'options',
                    'collection',
                    array(
                        'allow_delete' => false,
                        'allow_add' => false,
                        'type' => 'booking_option',
                        'by_reference' => false,
                        'prototype' => false,
                        /** @Ignore */
                        'label' => false,
                        'cascade_validation' => true,//Important to have error on collection item field!
                        'translation_domain' => 'cocorico_listing_option',
                    )
                );

            //Add new Listing Options eventually not already attached to booking
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    /** @var Booking $booking */
                    $booking = $event->getData();

                    $booking = $this->bookingManager->setBookingOptions(
                        $booking,
                        $this->locales,
                        $this->locale
                    );

                    $event->setData($booking);
                }
            );
        }

        /**
         * Message type
         */
        $builder
            ->add(
                'message',
                'textarea',
                array(
                    'label' => 'booking.form.message',
                    'required' => true
                )
            );

        //Dispatch BOOKING_NEW_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example by some payment provider bundle like mangopay
        $this->dispatcher->dispatch(BookingFormEvents::BOOKING_NEW_FORM_BUILD, new BookingFormBuilderEvent($builder));


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

        /**
         * Add errors to the form if any
         *
         * @param FormInterface $form
         * @param               $errors
         */
        $formErrors = function (FormInterface $form, $errors) {
            $keys = array_keys($errors, 'date_range.invalid.min_start');
            if (count($keys)) {
                foreach ($keys as $key) {
                    unset($errors[$key]);
                }
                $now = new \DateTime();
                if ($this->minStartDelay > 0) {
                    $now->add(new \DateInterval('P' . $this->minStartDelay . 'D'));
                }
                $form['date_range']->addError(
                    new FormError(
                        'date_range.invalid.min_start {{ min_start_day }}',
                        'cocorico',
                        array(
                            '{{ min_start_day }}' => $now->format('d/m/Y'),
                        )
                    )
                );
            }

            $keys = array_keys($errors, 'time_range.invalid.min_start');
            if (count($keys)) {
                foreach ($keys as $key) {
                    unset($errors[$key]);
                }
                $now = new \DateTime();
                if ($this->minStartTimeDelay > 0) {
                    $now->add(new \DateInterval('PT' . $this->minStartTimeDelay . 'H'));
                }
                $form['date_range']->addError(
                    new FormError(
                        'time_range.invalid.min_start {{ min_start_time }}',
                        'cocorico',
                        array(
                            '{{ min_start_time }}' => $now->format('d/m/Y H:i'),
                        )
                    )
                );
            }

            $keys = array_keys($errors, 'unavailable');
            if (count($keys)) {
                foreach ($keys as $key) {
                    unset($errors[$key]);
                }
                $form['date_range']->addError(
                    new FormError(self::$unavailableError)
                );
            }

            $keys = array_keys($errors, 'amount_invalid');
            if (count($keys)) {
                foreach ($keys as $key) {
                    unset($errors[$key]);
                }
                $form['date_range']->addError(
                    new FormError(
                        self::$amountError,
                        'cocorico',
                        array(
                            '{{ min_price }}' => $this->bookingManager->minPrice / 100 . " " . $this->currencySymbol,
                        )
                    )
                );
            }

            $keys = array_keys($errors, 'code_voucher_invalid');
            if (count($keys)) {
                foreach ($keys as $key) {
                    unset($errors[$key]);
                }
                $form['codeVoucher']->addError(
                    new FormError(
                        self::$voucherError,
                        'cocorico_booking',
                        array()
                    )
                );
            }

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form['date_range']->addError(
                        new FormError($error)
                    );
                }
            }

        };


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formUserModifier, $formErrors) {
                $form = $event->getForm();
                $formUserModifier($form);

                //todo: check if needed and not already made in BookingValidator class
                //Set Booking Amounts or throw Error if booking is invalid
                /** @var Booking $booking */
                $booking = $event->getData();
                $errors = $this->bookingManager->checkBookingAvailabilityAndSetAmounts($booking);

                if (!count($errors)) {
                    $event->setData($booking);
                } else {
                    $formErrors($form, $errors);
                }
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
                    /** @var $user User */
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

                $tac = $form->get('tac')->getData();
                if (empty($tac)) {
                    $form['tac']->addError(
                        new FormError(self::$tacError)
                    );
                }

                $message = $form->get('message')->getData();
                if (empty($message)) {
                    $form['message']->addError(
                        new FormError(self::$messageError)
                    );
                }
            }
        );


    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Booking',
                'intention' => 'booking_new',
                'translation_domain' => 'cocorico_booking',
                'cascade_validation' => true,
                'validation_groups' => array('new', 'default'),
            )
        );
    }

    /**
     * BC
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'booking_new';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        //todo: factorize cocorico and cocorico_booking error messages : move translation domain to cocorico_booking
        $messages = array();
        $messages[] = new Message(self::$tacError, 'cocorico');
        $messages[] = new Message(self::$messageError, 'cocorico');
        $messages[] = new Message(self::$unavailableError, 'cocorico');
        $messages[] = new Message(self::$amountError, 'cocorico');
        $messages[] = new Message(self::$voucherError, 'cocorico_booking');
        $messages[] = new Message(self::$credentialError, 'cocorico');

        return $messages;
    }
}

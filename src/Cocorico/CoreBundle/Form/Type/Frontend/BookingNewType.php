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
use Cocorico\TimeBundle\Form\Type\DateRangeType;
use Cocorico\TimeBundle\Form\Type\TimeRangeType;
use DateInterval;
use DateTime;
use DateTimeZone;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Valid;

class BookingNewType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'booking.form.tac.error';
    public static $messageError = 'booking.form.message.error';
    public static $unavailableError = 'booking.new.error.unavailable';
    public static $amountError = 'booking.new.error.amount_invalid {{ min_price }}';
    public static $voucherError = 'booking.new.error.voucher';
    public static $credentialError = 'user.form.credential.error';
    public static $messageDeliveryInvalid = 'booking.new.delivery.error';
    public static $messageDeliveryMaxInvalid = 'booking.new.delivery_max.error';

    private $bookingManager;
    private $dispatcher;
    private $allowSingleDay;
    private $endDayIncluded;
    private $minStartTimeDelay;
    private $acceptationDelay;
    private $currency;
    private $currencySymbol;
    private $addressDelivery;

    /**
     * @param BookingManager           $bookingManager
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        BookingManager $bookingManager,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->bookingManager = $bookingManager;
        $this->dispatcher = $dispatcher;

        $parameters = $parameters["parameters"];
        $this->allowSingleDay = $parameters['cocorico_booking_allow_single_day'];
        $this->endDayIncluded = $parameters['cocorico_booking_end_day_included'];
        $this->minStartTimeDelay = $parameters['cocorico_booking_min_start_time_delay'];
        $this->acceptationDelay = $parameters['cocorico_booking_acceptation_delay'];
        $this->currency = $parameters['cocorico_currency'];
        $this->currencySymbol = Intl::getCurrencyBundle()->getCurrencySymbol($this->currency);
        $this->addressDelivery = $parameters['cocorico_user_address_delivery'];
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
                DateRangeType::class,
                array(
                    'mapped' => false,
                    /** @Ignore */
                    'label' => false,
                    'required' => true,
                    'start_options' => array(
                        'label' => 'booking.form.start',
                        'mapped' => true,
                        'data' => $booking->getStart(),
                    ),
                    'end_options' => array(
                        'label' => 'booking.form.end',
                        'mapped' => true,
                        'data' => $booking->getEnd(),
                    ),
                    'allow_single_day' => $this->allowSingleDay,
                    'end_day_included' => $this->endDayIncluded,
                    'error_bubbling' => false,
                )
            )
            ->add(
                'tac',
                CheckboxType::class,
                array(
                    'label' => 'listing.form.tac',
                    'mapped' => false,
                    'constraints' => new IsTrue(
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
                TimeRangeType::class,
                array(
                    'mapped' => false,
                    'start_options' => array(
                        'mapped' => true,
                        'data' => $booking->getStartTime(),
                        'view_timezone' => 'UTC'
                    ),
                    'end_options' => array(
                        'mapped' => true,
                        'data' => $booking->getEndTime(),
                        'view_timezone' => 'UTC'
                    ),
                    'required' => true,
                    /** @Ignore */
                    'label' => false,
                    'block_name' => 'time_range_hidden',
                )
            );
        }

        /**
         * Message type
         */
        $builder
            ->add(
                'message',
                TextareaType::class,
                array(
                    'label' => 'booking.form.message',
                    'required' => true
                )
            );

        if ($this->addressDelivery) {
            $builder
                ->add(
                    'userAddress',
                    BookingUserAddressFormType::class,
                    array(
                        /** @Ignore */
                        'label' => false,
                        'required' => false,
                    )
                );
        }

        //Dispatch BOOKING_NEW_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example by user bundle to manage login / registration, some payment provider bundle like mangopay, ..
        $this->dispatcher->dispatch(BookingFormEvents::BOOKING_NEW_FORM_BUILD, new BookingFormBuilderEvent($builder));

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                //Set Booking Amounts or throw Error if booking is invalid
                /** @var Booking $booking */
                $booking = $event->getData();
                $result = $this->bookingManager->checkBookingAndSetAmounts($booking);
                $booking = $result->booking;
                $errors = $result->errors;

                if (!count($errors)) {
                    $event->setData($booking);
                } else {
                    $this->formErrors($form, $errors, $booking->getUser()->getTimeZone());
                }
            }
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

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
     * todo: decouple external bundles errors management
     *
     * Add errors to the form if any
     *
     * @param FormInterface $form
     * @param array         $errors
     * @param  string       $timezone
     */
    private function formErrors(FormInterface $form, $errors, $timezone)
    {
        $keys = array_keys($errors, 'date_range.invalid.min_start');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $minStart = new DateTime();
            $minStart->setTimezone(new DateTimeZone($timezone));
            if ($this->minStartTimeDelay > 0) {
                $minStart->add(new DateInterval('PT'.$this->minStartTimeDelay.'M'));
                $minStart->setTime(0, 0, 0);
            }
            $form['date_range']->addError(
                new FormError(
                    'date_range.invalid.min_start {{ min_start_day }}',
                    'cocorico',
                    array(
                        '{{ min_start_day }}' => $minStart->format('d/m/Y'),
                    )
                )
            );
        }

        $keys = array_keys($errors, 'date_range.invalid.acceptation');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $maxAcceptableDate = new DateTime();
            $maxAcceptableDate->setTimezone(new DateTimeZone($timezone));
            $maxAcceptableDate->add(new DateInterval('PT'.$this->acceptationDelay.'M'));
            $maxAcceptableDate->add(new DateInterval('P1D'));
            $form['date_range']->addError(
                new FormError(
                    'date_range.invalid.min_start {{ min_start_day }}',
                    'cocorico',
                    array(
                        '{{ min_start_day }}' => $maxAcceptableDate->format('d/m/Y'),
                    )
                )
            );
        }

        $keys = array_keys($errors, 'time_range.invalid.min_start');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $minStart = new DateTime();
            $minStart->setTimezone(new DateTimeZone($timezone));
            if ($this->minStartTimeDelay > 0) {
                $minStart->add(new DateInterval('PT'.$this->minStartTimeDelay.'M'));
            }
            $form['date_range']->addError(
                new FormError(
                    'time_range.invalid.min_start {{ min_start_time }}',
                    'cocorico',
                    array(
                        '{{ min_start_time }}' => $minStart->format('d/m/Y H:i'),
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

        $keys = array_keys($errors, 'delivery_invalid');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $form['deliveryAddress']->addError(
                new FormError(
                    self::$messageDeliveryInvalid,
                    'cocorico_booking',
                    array()
                )
            );
        }

        $keys = array_keys($errors, 'delivery_max_invalid');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $form['deliveryAddress']->addError(
                new FormError(
                    self::$messageDeliveryMaxInvalid,
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
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Booking',
                'csrf_token_id' => 'booking_new',
                'translation_domain' => 'cocorico_booking',
                'constraints' => new Valid(),
                'validation_groups' => array('new', 'default'),
            )
        );
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
        $messages[] = new Message(self::$messageDeliveryInvalid, 'cocorico_booking');
        $messages[] = new Message(self::$messageDeliveryMaxInvalid, 'cocorico_booking');

        return $messages;
    }
}

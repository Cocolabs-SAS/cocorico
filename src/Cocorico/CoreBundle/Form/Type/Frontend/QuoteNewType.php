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

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Event\QuoteFormBuilderEvent;
use Cocorico\CoreBundle\Event\QuoteFormEvents;
use Cocorico\CoreBundle\Model\Manager\QuoteManager;
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

class QuoteNewType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'quote.form.tac.error';
    public static $messageError = 'quote.form.message.error';
    public static $unavailableError = 'quote.new.error.unavailable';
    public static $credentialError = 'user.form.credential.error';
    public static $messageDeliveryInvalid = 'quote.new.delivery.error';
    public static $messageDeliveryMaxInvalid = 'quote.new.delivery_max.error';

    private $quoteManager;
    private $dispatcher;
    private $addressDelivery;

    /**
     * @param QuoteManager           $quoteManager
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        QuoteManager $quoteManager,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->quoteManager = $quoteManager;
        $this->dispatcher = $dispatcher;

        $parameters = $parameters["parameters"];
        $this->addressDelivery = $parameters['cocorico_user_address_delivery'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Quote $quote */
        $quote = $builder->getData();

        $builder
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

        /**
         * Message type
         */
        $builder
            ->add(
                'message',
                TextareaType::class,
                array(
                    'label' => 'quote.form.message',
                    'required' => true
                )
            );

        if ($this->addressDelivery) {
            $builder
                ->add(
                    'userAddress',
                    QuoteUserAddressFormType::class,
                    array(
                        /** @Ignore */
                        'label' => false,
                        'required' => false,
                    )
                );
        }

        //Dispatch QUOTE_NEW_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example by user bundle to manage login / registration, some payment provider bundle like mangopay, ..
        $this->dispatcher->dispatch(QuoteFormEvents::QUOTE_NEW_FORM_BUILD, new QuoteFormBuilderEvent($builder));

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                //Set Quote Amounts or throw Error if quote is invalid
                /** @var Quote $quote */
                // $quote = $event->getData();
                // $result = $this->quoteManager->checkQuoteAndSetAmounts($quote);
                // $quote = $result->quote;
                // $errors = $result->errors;

                // FIXME: Add error checking code
                /** @var Quote $quote */
                $quote = $event->getData();
                $errors = 0;

                if (!count($errors)) {
                    $event->setData($quote);
                } else {
                    $this->formErrors($form, $errors, $quote->getUser()->getTimeZone());
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Quote',
                'csrf_token_id' => 'quote_new',
                'translation_domain' => 'cocorico_quote',
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
        return 'quote_new';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        //todo: factorize cocorico and cocorico_quote error messages : move translation domain to cocorico_quote
        $messages = array();
        $messages[] = new Message(self::$tacError, 'cocorico');
        $messages[] = new Message(self::$messageError, 'cocorico');
        $messages[] = new Message(self::$unavailableError, 'cocorico');
        $messages[] = new Message(self::$amountError, 'cocorico');
        $messages[] = new Message(self::$voucherError, 'cocorico_quote');
        $messages[] = new Message(self::$credentialError, 'cocorico');
        $messages[] = new Message(self::$messageDeliveryInvalid, 'cocorico_quote');
        $messages[] = new Message(self::$messageDeliveryMaxInvalid, 'cocorico_quote');

        return $messages;
    }
}

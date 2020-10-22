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

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingLocation;
use Cocorico\CoreBundle\Event\ListingFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingFormEvents;
use Cocorico\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Cocorico\CoreBundle\Form\Type\ListingListingCharacteristicType;


/**
 * Class ListingNewType
 * Categories are created trough ajax in ListingNewCategoriesType.
 */
class ListingNewType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'listing.form.tac.error';
    public static $credentialError = 'user.form.credential.error';

    private $request;
    private $locale;
    private $locales;
    protected $dispatcher;
    protected $lem;

    /**
     * @param RequestStack             $requestStack
     * @param array                    $locales
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RequestStack $requestStack,
        $locales,
        ListingManager $lem,
        EventDispatcherInterface $dispatcher
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->locales = $locales;
        $this->dispatcher = $dispatcher;
        $this->lem = $lem;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Listing $listing */
        $listing = $builder->getData();

        //Translations fields
        $titles = $descriptions = array();
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = array(
                'label' => 'listing.form.title',
                'constraints' => array(new NotBlank(),
                                       new Length(
                                           array(
                                            'max' => 50,
                                            'min' => 3,
                                                )
                                            ),
                                       ),
                'attr' => array(
                    'placeholder' => 'auto',
                ),
            );
            $descriptions[$locale] = array(
                'label' => 'listing.form.description',
                'constraints' => array(new NotBlank()),
                'attr' => array(
                    'placeholder' => 'auto',
                ),
            );
        }

        $builder->add(
            'translations',
            TranslationsType::class,
            array(
                'required_locales' => array($this->locale),
                'fields' => array(
                    'title' => array(
                        'field_type' => 'text',
                        'locale_options' => $titles,
                    ),
                    'description' => array(
                        'field_type' => 'textarea',
                        'locale_options' => $descriptions,
                    ),
                    'rules' => array(
                        'display' => false,
                    ),
                    'slug' => array(
                        'display' => false
                    ),
                ),
                /** @Ignore */
                'label' => false,
            )
        );

        $builder
            ->add(
                'range',
                IntegerType::class,
                array(
                    'label' => 'listing.form.range',
                    'required' => false,
                )
            )
            ->add(
                'polRange',
                ChoiceType::class,
                array(
                    'choices' => array_flip(Listing::$polRangeValues),
                    'label' => 'Périmêtre intervention',
                    'translation_domain' => 'cocorico_listing',
                    'expanded' => true,
                    'required' => true
                )
            )
            ->add(
                'url',
                UrlType::class,
                array(
                    'label' => 'listing.form.url',
                    'required' => false,
                )
            )
            ->add(
                'presta_type',
                ChoiceType::class,
                array(
                    'choices' => array_flip(Listing::$prestaTypeValues),
                    'label' => 'Type prestation',
                    'translation_domain' => 'cocorico_listing',
                    'expanded' => true,
                    'required' => true
                )
            )
            ->add(
                'schedule_before_opening',
                CheckBoxType::class,
                array(
                    'label' => 'Avant ouverture'
                )
            )
            ->add(
                'schedule_business_hours',
                CheckBoxType::class,
                array(
                    'label' => 'Heures de bureau'
                )
            )
            ->add(
                'schedule_after_closing',
                CheckBoxType::class,
                array(
                    'label' => 'Après fermeture'
                )
            )
            ->add(
                'image',
                ImageType::class
            )
            ->add(
                'clientImage',
                ImageType::class
            );

        //Default listing location
        $listingLocation = null;
        $user = $listing->getUser();
        if ($user) {
            if ($user->getListings()->count()) {
                /** @var Listing $listing */
                $listing = $user->getListings()->first();
                $location = $listing->getLocation();

                $listingLocation = new ListingLocation();
                $listingLocation->setListing($listing);
                $listingLocation->setCountry($location->getCountry());
                $listingLocation->setCity($location->getCity());
                $listingLocation->setZip($location->getZip());
                $listingLocation->setRoute($location->getRoute());
                $listingLocation->setStreetNumber($location->getStreetNumber());
            }
        }

        $builder
            ->add(
                'location',
                ListingLocationType::class,
                array(
                    'data_class' => 'Cocorico\CoreBundle\Entity\ListingLocation',
                    /** @Ignore */
                    'label' => false,
                    'data' => $listingLocation,
                )
            );

        $builder
            ->add(
                'tac',
                CheckboxType::class,
                array(
                    'label' => 'listing.form.tac',
                    'mapped' => false,
                    'constraints' => new IsTrue(
                        array(
                            'message' => self::$tacError,
                        )
                    ),
                )
            );

        $builder
            //->add('listingListingCharacteristicsOrderedByGroup',
            //    ListingCharacteristicType::class,
            //    array (
            //        'mapped' => false
            //    )
            //);
            ->add(
                'listingListingCharacteristicsOrderedByGroup',
                CollectionType::class,
                array(
                    'entry_type' => ListingListingCharacteristicType::class,
                    # 'entry_options' => [
                    #     'multiple' => True
                    # ],
                    /** @Ignore */
                    'label' => false
                )
            );

        //Add new ListingCharacteristics eventually not already attached to listing
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Listing $listing */
                $listing = $event->getData();
                ### FIXME: Hack, missing locale gives lots of errors later on

                $listing = $this->lem->refreshListingListingCharacteristics($listing);
                $event->setData($listing);
            }
        );


        // Dispatch LISTING_NEW_FORM_BUILD Event. Listener listening this event can add fields and validation
        // Used for example to add fields to new listing form
        $this->dispatcher->dispatch(
            ListingFormEvents::LISTING_NEW_FORM_BUILD,
            new ListingFormBuilderEvent($builder)
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'allow_extra_fields' => true,
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'csrf_token_id' => 'listing_new',
                'translation_domain' => 'cocorico_listing',
                'constraints' => new Valid(),
                //'validation_groups' => array('Listing'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_new';
    }

    /**
     * JMS Translation messages.
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$tacError, 'cocorico');
        $messages[] = new Message(self::$credentialError, 'cocorico');

        return $messages;
    }
}

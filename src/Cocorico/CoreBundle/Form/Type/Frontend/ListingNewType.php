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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingLocation;
use Cocorico\CoreBundle\Event\ListingFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingFormEvents;
use Cocorico\CoreBundle\Form\Type\ImageType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

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

    /**
     * @param RequestStack             $requestStack
     * @param array                    $locales
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RequestStack $requestStack,
        $locales,
        EventDispatcherInterface $dispatcher
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->locales = $locales;
        $this->dispatcher = $dispatcher;
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
                'attr' => array(
                    'placeholder' => 'auto',
                ),
            );
            $descriptions[$locale] = array(
                'label' => 'listing.form.description',
                'attr' => array(
                    'placeholder' => 'auto',
                ),
            );
        }

        $builder->add(
            'translations',
            'a2lix_translations',
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
                        'field_type' => 'hidden',
                    ),
                ),
                /** @Ignore */
                'label' => false,
            )
        );

        $builder
            ->add(
                'price',
                'price',
                array(
                    'label' => 'listing.form.price',
                )
            )
            ->add(
                'image',
                new ImageType()
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
                new ListingLocationType(),
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
                'checkbox',
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

        //Dispatch LISTING_NEW_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example to add fields to new listing form
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'csrf_token_id' => 'listing_new',
                'translation_domain' => 'cocorico_listing',
                'cascade_validation' => true,
                //'validation_groups' => array('Listing'),
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

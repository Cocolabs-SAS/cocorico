<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\ListingListingCharacteristicType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ListingEditCharacteristicType
 */
class ListingEditCharacteristicType extends ListingEditType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'listingListingCharacteristicsOrderedByGroup',
                CollectionType::class,
                array(
                    'entry_type' => ListingListingCharacteristicType::class,
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
                $listing = $this->lem->refreshListingListingCharacteristics($listing);
                $event->setData($listing);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_edit_characteristic';
    }
}

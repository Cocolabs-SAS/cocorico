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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ListingLocationType
 */
class ListingLocationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'country',
                'country_filtered',
                array(
                    'label' => 'listing.form.location.country',
                    'translation_domain' => 'cocorico_listing',//Not recognized elsewhere
                    'required' => true,
                )
            )
            ->add(
                'city',
                'text',
                array(
                    'label' => 'listing.form.location.city',
                    'translation_domain' => 'cocorico_listing',
                    'required' => true,
                )
            )
            ->add(
                'zip',
                'text',
                array(
                    'label' => 'listing.form.location.zip',
                    'translation_domain' => 'cocorico_listing',
                    'required' => false,
                )
            )
            ->add(
                'route',
                'text',
                array(
                    'label' => 'listing.form.location.route',
                    'translation_domain' => 'cocorico_listing',
                    'required' => true,
                )
            )
            ->add(
                'street_number',
                'text',
                array(
                    'label' => 'listing.form.location.street_number',
                    'translation_domain' => 'cocorico_listing',
                    'required' => true,
                )
            )
            //This field contains geocoding information in JSON format.
            //Its value is transformed to Coordinate entity through data transformer
            ->add(
                'coordinate',
                'geocoding_to_coordinate'
            );

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'translation_domain' => 'cocorico_listing',
                'data_class' => 'Cocorico\CoreBundle\Entity\ListingLocation',
                'cascade_validation' => true,
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
        return 'listing_location';
    }
}

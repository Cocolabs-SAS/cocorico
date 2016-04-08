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

use Cocorico\CoreBundle\Form\Type\Frontend\ListingLocationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ListingEditVariousInformationType
 */
class ListingEditVariousInformationType extends ListingEditType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add(
            //     'type',
            //     'choice',
            //     array(
            //         'choices' => array_flip(Listing::$typeValues),
            //         'empty_value' => 'listing.form.various_information.choose',
            //         'required' => false,
            //         'translation_domain' => 'cocorico_listing',
            //         'label' => 'listing.form.type',
            //            'choices_as_values' => true
            //     )
            // )
            ->add(
                'categories',
                'listing_category',
                array(
                    'block_name' => 'categories'
                )
            )
            ->add(
                'location',
                new ListingLocationType(),
                array(
                    'data_class' => 'Cocorico\CoreBundle\Entity\ListingLocation',
                    /** @Ignore */
                    'label' => false,

                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
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
        return 'listing_edit_various_information';
    }

}

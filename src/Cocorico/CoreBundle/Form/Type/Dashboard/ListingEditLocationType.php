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
 * ListingEditLocationType
 */
class ListingEditLocationType extends ListingEditType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'location',
                ListingLocationType::class,
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_edit_location';
    }

}

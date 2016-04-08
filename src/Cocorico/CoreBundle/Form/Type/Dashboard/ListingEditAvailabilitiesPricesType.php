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
use Cocorico\CoreBundle\Validator\Constraints\ListingAvailabilitiesPrice;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ListingEditAvailabilitiesPricesType extends ListingEditAvailabilitiesType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        /** @var Listing $listing */
        $listing = $builder->getData();
        $builder
            ->add(
                'price_custom',
                'price',
                array(
                    'label' => 'listing_edit.form.price_custom',
                    'mapped' => false,
                    'required' => true,
                    'data' => is_null($listing->getPrice()) ? null : $listing->getPrice(),
                    'constraints' => array(
                        new ListingAvailabilitiesPrice(),
                    )
                )
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
        return 'listing_edit_availabilities_prices';
    }

}

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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingSearchHomeType extends ListingSearchResultType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('date_range')
            ->remove('price_range')
            ->remove('characteristics')
            ->remove('sort_by')
            ->remove('delivery')
            ->remove('categories_fields');

        if ($this->timeUnitFlexibility) {
            $builder->remove('flexibility');
        }

        if (!$this->timeUnitIsDay) {
            $builder->remove('time_range');
        }

    }

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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_search_home';
    }

}

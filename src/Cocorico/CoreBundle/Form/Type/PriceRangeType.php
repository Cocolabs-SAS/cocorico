<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class PriceRangeType extends AbstractType
{
    protected $currency;

    public function __construct($currency)
    {
        $this->currency = $currency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'min',
                'price',
                array(
                    'label' => 'listing.form.price',
                    'currency' => $this->currency,
                    'precision' => 0
                )
            )
            ->add(
                'max',
                'price',
                array(
                    /** @Ignore */
                    'label' => false,
                    'currency' => $this->currency,
                    'precision' => 0
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'cocorico_listing',
                'data_class' => 'Cocorico\CoreBundle\Model\PriceRange',
            )
        );
    }

    public function getName()
    {
        return 'price_range';
    }
}

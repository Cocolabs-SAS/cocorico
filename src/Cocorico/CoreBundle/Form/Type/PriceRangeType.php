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
use Symfony\Component\OptionsResolver\OptionsResolver;


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
                    'scale' => 0
                )
            )
            ->add(
                'max',
                'price',
                array(
                    /** @Ignore */
                    'label' => false,
                    'currency' => $this->currency,
                    'scale' => 0
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'cocorico_listing',
                'data_class' => 'Cocorico\CoreBundle\Model\PriceRange',
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
        return 'price_range';
    }
}

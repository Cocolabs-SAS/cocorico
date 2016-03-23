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

class ListingDiscountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'discount',
                'integer',
                array(
                    'label' => 'listing_edit.form.discount',
                    'attr' => array(
                        //todo: use parameters cocorico.listing_discount_xxx instead
                        'min' => '1',
                        'max' => '90',
                    ),
                    'required' => true
                )
            )
            ->add(
                'fromQuantity',
                'integer',
                array(
                    'label' => 'listing_edit.form.from_quantity',
                    'attr' => array(
                        //todo: use parameters cocorico.listing_discount_xxx instead
                        'min' => '1',
                        'max' => '90',
                    ),
                    'required' => true
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\ListingDiscount',
                'translation_domain' => 'cocorico_listing',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'listing_discount';
    }
}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'address',
                'textarea',
                array(
                    'label' => 'form.address.address',
                    'required' => false
                )
            )
            ->add(
                'city',
                null,
                array(
                    'label' => 'form.address.city',
                    'required' => true
                )
            )
            ->add(
                'zip',
                null,
                array(
                    'label' => 'form.address.zip',
                    'required' => true
                )
            )
            ->add(
                'country',
                'country',
                array(
                    'label' => 'form.address.country',
                    'required' => true,
                    'data' => 'FR',
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                )
            );


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\UserBundle\Entity\UserAddress',
                'intention' => 'billing',
                'translation_domain' => 'cocorico_user',
                'cascade_validation' => true
            )
        );
    }

    public function getName()
    {
        return 'user_address';
    }

}

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
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class UserAddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'address',
                TextareaType::class,
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
                    'required' => false
                )
            )
            ->add(
                'zip',
                null,
                array(
                    'label' => 'form.address.zip',
                    'required' => false
                )
            )
            ->add(
                'country',
                CountryType::class,
                array(
                    'label' => 'form.address.country',
                    'required' => false,
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                )
            );


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\UserBundle\Entity\UserAddress',
                'csrf_token_id' => 'user_address',
                'translation_domain' => 'cocorico_user',
                'constraints' => new Valid(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_address';
    }
}

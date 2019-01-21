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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProfileContactFormType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                'email',
                array(
                    'label' => 'form.user.email'
                )
            )
            ->add(
                'phonePrefix',
                'text',
                array(
                    'label' => 'form.user.phone_prefix',
                    'required' => false,
                    'empty_data' => '+33'
                )
            )
            ->add(
                'phone',
                'text',
                array(
                    'label' => 'form.user.phone',
                    'required' => false
                )
            )
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type' => 'password',
                    'options' => array('translation_domain' => 'cocorico_user'),
                    'first_options' => array(
                        'label' => 'form.password',
                        'required' => true
                    ),
                    'second_options' => array(
                        'label' => 'form.password_confirmation',
                        'required' => true
                    ),
                    'invalid_message' => 'fos_user.password.mismatch',
                    'required' => false
                )
            )
            ->add(
                'addresses',
                'collection',
                array(
                    'entry_type' => UserAddressFormType::class,
                    /** @Ignore */
                    'label' => false,
                    'required' => false,
                )
            );


    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\UserBundle\Entity\User',
                'csrf_token_id' => 'profile',
                'translation_domain' => 'cocorico_user',
                'constraints' => new Valid(),
                'validation_groups' => array('CocoricoProfileContact'),
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_profile_contact';
    }
}

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

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationFormType.
 */
class RegistrationFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'personType',
                ChoiceType::class,
                array(
                    'label' => 'form.person_type',
                    'choices' => array_flip(User::$personTypeValues),
                    'expanded' => true,
                    'empty_data' => User::PERSON_TYPE_NATURAL,
                    'required' => true,
                    'choices_as_values' => true
                )
            )
            ->add(
                'companyName',
                null,
                array(
                    'label' => 'form.company_name',
                    'required' => false,
                )
            )
            ->add(
                'lastName',
                null,
                array('label' => 'form.last_name')
            )
            ->add(
                'firstName',
                null,
                array('label' => 'form.first_name')
            )
            ->add(
                'phonePrefix',
                'text',
                array(
                    'label' => 'form.user.phone_prefix',
                    'required' => false,
                    'empty_data' => '+33',
                )
            )
            ->add(
                'phone',
                'text',
                array(
                    'label' => 'form.user.phone',
                    'required' => false,
                )
            )
            ->add(
                'email',
                'email',
                array('label' => 'form.email')
            )
            ->add(
                'birthday',
                'birthday',
                array(
                    'label' => 'form.user.birthday',
                    'widget' => 'choice',
                    'years' => range(date('Y') - 18, date('Y') - 100),
                    'required' => true,
                )
            )
            ->add(
                'nationality',
                'country',
                array(
                    'label' => 'form.user.nationality',
                    'required' => false,
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                )
            )
            ->add(
                'countryOfResidence',
                'country',
                array(
                    'label' => 'form.user.countryOfResidence',
                    'required' => true,
                    'preferred_choices' => array('GB', 'FR', 'ES', 'DE', 'IT', 'CH', 'US', 'RU'),
                    'data' => 'FR',
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
                        'required' => true,
                    ),
                    'second_options' => array(
                        'label' => 'form.password_confirmation',
                        'required' => true,
                    ),
                    'invalid_message' => 'fos_user.password.mismatch',
                    'required' => true,
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->class,
                'csrf_token_id' => 'user_registration',
                'translation_domain' => 'cocorico_user',
                'validation_groups' => array('CocoricoRegistration'),
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
        return 'user_registration';
    }
}

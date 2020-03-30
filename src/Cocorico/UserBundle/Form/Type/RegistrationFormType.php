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
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationFormType.
 */
class RegistrationFormType extends AbstractType
{
    protected $timeUnitIsDay;

    /**
     * RegistrationFormType constructor.
     * @param $timeUnit
     */
    public function __construct($timeUnit)
    {
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
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
                )
            )
            ->add(
                'companyName',
                TextType::class,
                array(
                    'label' => 'form.company_name',
                    'required' => false,
                )
            )
            ->add(
                'lastName',
                TextType::class,
                array(
                    'label' => 'form.last_name',
                )
            )
            ->add(
                'firstName',
                TextType::class,
                array(
                    'label' => 'form.first_name',
                )
            )
            ->add(
                'phonePrefix',
                TextType::class,
                array(
                    'label' => 'form.user.phone_prefix',
                    'required' => false,
                    'empty_data' => '+33',
                )
            )
            ->add(
                'phone',
                TelType::class,
                array(
                    'label' => 'form.user.phone',
                    'required' => false,
                )
            )
            ->add(
                'email',
                EmailType::class,
                array('label' => 'form.email')
            )
            ->add(
                'birthday',
                BirthdayType::class,
                array(
                    'label' => 'form.user.birthday',
                    'widget' => 'choice',
                    'years' => range(date('Y') - 18, date('Y') - 100),
                    'required' => true,
                )
            )
            ->add(
                'nationality',
                CountryType::class,
                array(
                    'label' => 'form.user.nationality',
                    'required' => true,
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                )
            )
            ->add(
                'countryOfResidence',
                CountryType::class,
                array(
                    'label' => 'form.user.country_of_residence',
                    'required' => true,
                    'preferred_choices' => array('GB', 'FR', 'ES', 'DE', 'IT', 'CH', 'US', 'RU'),
                    'data' => 'FR',
                )
            )
            ->add(
                'plainPassword',
                RepeatedType::class,
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

        if (!$this->timeUnitIsDay) {
            $builder
                ->add(
                    'timeZone',
                    TimezoneType::class,
                    array(
                        'label' => 'form.time_zone',
                        'required' => true,
                    )
                );
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\UserBundle\Entity\User',
                'csrf_token_id' => 'user_registration',
                'translation_domain' => 'cocorico_user',
                'validation_groups' => array('CocoricoRegistration'),
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_registration';
    }
}

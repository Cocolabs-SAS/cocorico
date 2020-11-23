<?php

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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationFormType.
 */
class FlashFormType extends AbstractType
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
                HiddenType::class,
                array(
                    'data' => User::PERSON_TYPE_CLASSIC
                ),
            )
            ->add(
                'companyName',
                TextType::class,
                array(
                    'label' => 'form.company_name',
                    'required' => true,
                )
            )
            ->add(
                'companyAddrString',
                TextType::class,
                array(
                    'label' => 'form.company_addr_string',
                    'required' => true,
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
                'lastName',
                HiddenType::class,
                array(
                    'data' => '--------'
                ),
            )
            # ->add(
            #     'phonePrefix',
            #     TextType::class,
            #     array(
            #         'label' => 'form.user.phone_prefix',
            #         'required' => false,
            #         'empty_data' => '+33',
            #     )
            # )
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
                'accept_rgpd',
                CheckboxType::class,
                array(
                    'label' => 'form.accept_rgpd',
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
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'allow_extra_fields' => true,
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

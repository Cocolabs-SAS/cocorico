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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProfileContactFormType extends AbstractType
{
    protected $timeUnitIsDay;

    /**
     * ProfileContactFormType constructor.
     * @param $timeUnit
     */
    public function __construct($timeUnit)
    {
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                array(
                    'label' => 'form.user.email'
                )
            )
            ->add(
                'phonePrefix',
                TextType::class,
                array(
                    'label' => 'form.user.phone_prefix',
                    'required' => false,
                    'empty_data' => '+33'
                )
            )
            ->add(
                'phone',
                TelType::class,
                array(
                    'label' => 'form.user.phone',
                    'required' => false
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
                CollectionType::class,
                array(
                    'entry_type' => UserAddressFormType::class,
                    /** @Ignore */
                    'label' => false,
                    'required' => false,
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

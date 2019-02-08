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

use Cocorico\CoreBundle\Form\Type\PriceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProfileBankAccountFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'lastName',
                TextareaType::class,
                array(
                    'label' => 'form.user.last_name',
                )
            )
            ->add(
                'firstName',
                TextType::class,
                array(
                    'label' => 'form.user.first_name'
                )
            )
            ->add(
                'birthday',
                BirthdayType::class,
                array(
                    'label' => 'form.user.birthday',
                    'widget' => "choice",
                    'years' => range(date('Y') - 18, date('Y') - 100),
                    'required' => true
                )
            )
            ->add(
                'nationality',
                CountryType::class,
                array(
                    'label' => 'form.user.nationality',
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                )
            )
            ->add(
                'countryOfResidence',
                CountryType::class,
                array(
                    'label' => 'form.user.country_of_residence',
                    'required' => true,
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                )
            )
            ->add(
                'profession',
                TextType::class,
                array(
                    'label' => 'form.user.profession',
                    'required' => false
                )
            )
            ->add(
                'annualIncome',
                PriceType::class,
                array(
                    'label' => 'form.user.annual_income',
                    'translation_domain' => 'cocorico_user',
                    'required' => false
                )
            )
            ->add(
                'bankOwnerName',
                TextType::class,
                array(
                    'label' => 'form.user.bank_owner_name',
                    'required' => true,
                )
            )
            ->add(
                'bankOwnerAddress',
                TextareaType::class,
                array(
                    'label' => 'form.user.bank_owner_address',
                    'required' => true,
                )
            )
            ->add(
                'iban',
                TextType::class,
                array(
                    'label' => 'IBAN',
                    'required' => true
                )
            )
            ->add(
                'bic',
                TextType::class,
                array(
                    'label' => 'BIC',
                    'required' => true
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
                'csrf_token_id' => 'CocoricoProfileBankAccount',
                'translation_domain' => 'cocorico_user',
                'constraints' => new Valid(),
                'validation_groups' => array('CocoricoProfileBankAccount'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_profile_bank_account';
    }
}

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

/**
 * Class RegistrationFormType
 *
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
                'email',
                'email',
                array('label' => 'form.email')
            )
            ->add(
                'birthday',
                'birthday',
                array(
                    'label' => 'form.user.birthday',
//                    'format' => 'dd MMMM yyyy',
                    'widget' => "choice",
                    'years' => range(date('Y') - 18, date('Y') - 100),
                    'required' => true
                )
            )
            ->add(
                'countryOfResidence',
                'country',
                array(
                    'label' => 'form.user.countryOfResidence',
                    'required' => true,
                    'data' => 'FR',
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
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
                    'required' => true
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->class,
                'intention' => 'user_registration',
                'translation_domain' => 'cocorico_user',
                'validation_groups' => array('CocoricoRegistration'),
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user_registration';
    }
}

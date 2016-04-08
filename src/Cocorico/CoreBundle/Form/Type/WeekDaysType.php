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
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeekDaysType extends AbstractType
{

    public function __construct()
    {

    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'mapped' => false,
                'choices' => array(
                    'cocorico.monday' => '1',
                    'cocorico.tuesday' => '2',
                    'cocorico.wednesday' => '3',
                    'cocorico.thursday' => '4',
                    'cocorico.friday' => '5',
                    'cocorico.saturday' => '6',
                    'cocorico.sunday' => '7',
                ),
                'translation_domain' => 'cocorico',
                'multiple' => true,
                'expanded' => true,
                /** @Ignore */
                'label' => false,
                'data' => array('1', '2', '3', '4', '5', '6', '7'),
                'choices_as_values' => true
            )
        );
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
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
        return 'weekdays';
    }
}

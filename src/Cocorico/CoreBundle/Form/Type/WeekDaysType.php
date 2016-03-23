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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'mapped' => false,
                'choices' => array(
                    '1' => 'cocorico.monday',
                    '2' => 'cocorico.tuesday',
                    '3' => 'cocorico.wednesday',
                    '4' => 'cocorico.thursday',
                    '5' => 'cocorico.friday',
                    '6' => 'cocorico.saturday',
                    '7' => 'cocorico.sunday',
                ),
                'translation_domain' => 'cocorico',
                'multiple' => true,
                'expanded' => true,
                /** @Ignore */
                'label' => false,
                'data' => array('1', '2', '3', '4', '5', '6', '7')
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
     * @return string
     */
    public function getName()
    {
        return 'weekdays';
    }

}

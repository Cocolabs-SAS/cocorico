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

class ImageType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder->create(
                    'new',
                    'file',
                    array(
                        'mapped' => false,
                        //'property_path' => 'images',
                        'required' => false,
                        'multiple' => true,
                    )
                )
            )
            ->add(
                'uploaded',
                'hidden',
                array(
                    'mapped' => false,
                    'required' => false,
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
                'mapped' => false,
                /** @Ignore */
                'label' => false
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image';
    }

}

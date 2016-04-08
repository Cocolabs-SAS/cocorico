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

class UserImageType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'hidden',
                array(
                    /** @Ignore */
                    'label' => false
                )
            )
            ->add(
                'file',
                'file',
                array(
                    'image_path' => 'webPath',
                    'imagine_filter' => 'user_small',
                    /** @Ignore */
                    'label' => false,
                    'mapped' => false,
                    'attr' => array(
                        "class" => "dn"
                    )
                )
            )
            ->add(
                'position',
                'hidden',
                array(
                    /** @Ignore */
                    'label' => false,
                    'attr' => array(
                        "class" => "sort-position"
                    )
                )
            )
            ->add(
                'user',
                'entity_hidden',
                array(
                    'class' => 'Cocorico\UserBundle\Entity\User',
                    /** @Ignore */
                    'label' => false
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
                'data_class' => 'Cocorico\UserBundle\Entity\UserImage',
                'csrf_token_id' => 'user_image',
                'translation_domain' => 'cocorico_user',
                'cascade_validation' => true,
                /** @Ignore */
                'label' => false
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
        return 'user_image';
    }

}

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

use Cocorico\CoreBundle\Entity\Listing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ListingImageType extends AbstractType
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
                    'imagine_filter' => 'listing_xxmedium',
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
                'listing',
                'entity_hidden',
                array(
                    'class' => 'Cocorico\CoreBundle\Entity\Listing',
                    /** @Ignore */
                    'label' => false
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
                'data_class' => 'Cocorico\CoreBundle\Entity\ListingImage',
                'intention' => 'listing_image',
                'translation_domain' => 'cocorico_listing',
                'cascade_validation' => true,
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
        return 'listing_image';
    }

}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingEditCategoriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

//        $builder
//            ->add(
//                'type',
//                'choice',
//                array(
//                    'choices' => array_flip(Listing::$typeValues),
//                    'empty_value' => 'listing_edit.form.choose',
//                    'required' => false,
//                    'translation_domain' => 'cocorico_listing',
//                    'label' => 'listing.form.type',
//                    'choices_as_values' => true
//                )
//            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'csrf_token_id' => 'listing_edit',
                'translation_domain' => 'cocorico_listing',
                'cascade_validation' => true,//To have error on collection item field,
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
        return 'listing_edit_categories';
    }

}

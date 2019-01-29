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

use Cocorico\CoreBundle\Entity\Listing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingEditDurationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'min_duration',
                ChoiceType::class,
                array(
                    'label' => 'listing_edit.form.min_duration',
                    'choices' => array_combine(range(1, 30), range(1, 30)),
                    'placeholder' => 'listing_edit.form.choose',
                    'empty_data' => null,
                    'required' => false,
                )
            )
            ->add(
                'max_duration',
                ChoiceType::class,
                array(
                    'label' => 'listing_edit.form.max_duration',
                    'choices' => array_combine(range(1, 30), range(1, 30)),
                    'placeholder' => 'listing_edit.form.choose',
                    'empty_data' => null,
                    'required' => false,
                )
            )
            ->add(
                'cancellation_policy',
                ChoiceType::class,
                array(
                    'label' => 'listing_edit.form.cancellation_policy',
                    'choices' => array_flip(Listing::$cancellationPolicyValues),
                )
            );
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
                'translation_domain' => 'cocorico_listing',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_edit_duration';
    }
}

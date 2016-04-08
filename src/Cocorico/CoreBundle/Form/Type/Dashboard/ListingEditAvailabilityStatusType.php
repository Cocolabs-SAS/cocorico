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

use Cocorico\CoreBundle\Document\ListingAvailability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingEditAvailabilityStatusType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'status',
                'choice',
                array(
                    'choices' => array_flip(ListingAvailability::$visibleValues),
                    'choices_as_values' => true
                )
            );

        //Set default price for new availability
        $defaultPrice = $options["defaultPrice"];
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($defaultPrice) {
                /** @var ListingAvailability $availability */
                $availability = $event->getData();
                $form = $event->getForm();

                if ((!$availability || null === $availability->getId()) && $defaultPrice) {
                    $form->add('price', 'hidden');
                    if ($availability) {
                        $availability->setPrice($defaultPrice);
                    }
                    $event->setData($availability);
                }
            }
        );


    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Document\ListingAvailability',
                'translation_domain' => 'cocorico_listing',
                'defaultPrice' => null
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
        return 'listing_edit_availability_status';
    }
}

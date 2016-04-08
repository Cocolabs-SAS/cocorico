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

use Cocorico\CoreBundle\Validator\Constraints\TimeRangesOverlap;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ListingEditAvailabilitiesType extends AbstractType
{

    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $daysMaxEdition;

    /**
     * @param int $timeUnit
     * @param int $daysMaxEdition
     */
    public function __construct($timeUnit, $daysMaxEdition)
    {
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->daysMaxEdition = $daysMaxEdition;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'date_range',
                'date_range',
                array(
                    'mapped' => false,
                    /** @Ignore */
                    'label' => false,
                    'required' => true,
                    'start_options' => array(
                        'label' => 'listing.form.start',
                    ),
                    'end_options' => array(
                        'label' => 'listing.form.end',
                    ),
                    'error_bubbling' => false,
                    'days_max' => $this->daysMaxEdition
                )
            )
            ->add(
                'weekdays',
                'weekdays'
            );

        if (!$this->timeUnitIsDay) {
            $builder->add(
                'time_ranges',
                'collection',
                array(
                    'mapped' => false,
                    'type' => 'time_range',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'prototype' => true,
                    /** @Ignore */
                    'label' => false,
                    'constraints' => array(
                        new TimeRangesOverlap(array('min' => 1)),
                    ),
                    'error_bubbling' => false,
                )
            );
        }

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'translation_domain' => 'cocorico_listing',
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
        return 'listing_edit_availabilities';
    }
}

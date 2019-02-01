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

use Cocorico\TimeBundle\Form\Type\DateRangeType;
use Cocorico\TimeBundle\Form\Type\TimeRangeType;
use Cocorico\TimeBundle\Form\Type\WeekDaysType;
use Cocorico\TimeBundle\Validator\Constraints\TimeRangesOverlap;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                DateRangeType::class,
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
                WeekDaysType::class
            );

        if (!$this->timeUnitIsDay) {
            $builder->add(
                'time_ranges',
                CollectionType::class,
                array(
                    'mapped' => false,
                    'entry_type' => TimeRangeType::class,
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_edit_availabilities';
    }
}

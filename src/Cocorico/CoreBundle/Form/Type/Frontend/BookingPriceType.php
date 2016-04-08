<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingPriceType extends AbstractType
{
    protected $bookingManager;
    protected $allowSingleDay;
    protected $endDayIncluded;
    protected $daysDisplayMode;
    protected $timesDisplayMode;

    /**
     * @param BookingManager $bookingManager
     * @param bool           $allowSingleDay
     * @param bool           $endDayIncluded
     * @param string         $daysDisplayMode
     * @param string         $timesDisplayMode
     */
    public function __construct(
        BookingManager $bookingManager,
        $allowSingleDay,
        $endDayIncluded,
        $daysDisplayMode,
        $timesDisplayMode
    ) {
        $this->bookingManager = $bookingManager;
        $this->allowSingleDay = $allowSingleDay;
        $this->endDayIncluded = $endDayIncluded;
        $this->daysDisplayMode = $daysDisplayMode;
        $this->timesDisplayMode = $timesDisplayMode;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Booking $booking */
        $booking = $builder->getData();

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
                        'label' => 'booking.form.start',
                        'mapped' => true,
                        'data' => $booking->getStart(),
                        'required' => true,
                    ),
                    'end_options' => array(
                        'label' => 'booking.form.end',
                        'mapped' => true,
                        'data' => $booking->getEnd(),
                        'required' => true,
                    ),
                    'allow_single_day' => $this->allowSingleDay,
                    'end_day_included' => $this->endDayIncluded,
                    'block_name' => 'date_range_ajax',
                    'display_mode' => $this->daysDisplayMode
                )

            );

        if (!$this->bookingManager->getTimeUnitIsDay()) {
            $builder->add(
                'time_range',
                'time_range',
                array(
                    'mapped' => false,
                    'start_options' => array(
                        'mapped' => true,
                        'data' => $booking->getStartTime()
                    ),
                    'end_options' => array(
                        'mapped' => true,
                        'data' => $booking->getEndTime()
                    ),
                    'block_name' => 'time_range_ajax',
                    'required' => true,
                    /** @Ignore */
                    'label' => false,
                    'display_mode' => $this->timesDisplayMode
                )
            );
        }
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Booking',
                'csrf_token_id' => 'booking_price',
                'translation_domain' => 'cocorico_booking',
                'cascade_validation' => true,
                'validation_groups' => array('new'),
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
        return 'booking_price';
    }

}

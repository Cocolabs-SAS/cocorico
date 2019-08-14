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
use Cocorico\TimeBundle\Form\Type\DateRangeType;
use Cocorico\TimeBundle\Form\Type\TimeRangeType;
use Cocorico\TimeBundle\Model\DateRange;
use Cocorico\TimeBundle\Model\DateTimeRange;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class BookingPriceType extends AbstractType
{
    protected $allowSingleDay;
    protected $endDayIncluded;
    protected $daysDisplayMode;
    protected $timesDisplayMode;
    protected $timeUnitIsDay;

    /**
     * @param bool   $allowSingleDay
     * @param bool   $endDayIncluded
     * @param string $daysDisplayMode
     * @param string $timesDisplayMode
     * @param int    $timeUnit
     */
    public function __construct(
        $allowSingleDay,
        $endDayIncluded,
        $daysDisplayMode,
        $timesDisplayMode,
        $timeUnit
    ) {
        $this->allowSingleDay = $allowSingleDay;
        $this->endDayIncluded = $endDayIncluded;
        $this->daysDisplayMode = $daysDisplayMode;
        $this->timesDisplayMode = $timesDisplayMode;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
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
                DateRangeType::class,
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

        if (!$this->timeUnitIsDay) {
            $builder->add(
                'time_range',
                TimeRangeType::class,
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

        //Sync date and time
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var Booking $booking */
                $booking = $event->getData();
                $form = $event->getForm();

                /** @var DateRange $dateRange */
                $dateRange = clone $form->get('date_range')->getData();
                $booking->setStart($dateRange->getStart());
                $booking->setEnd($dateRange->getEnd());
                $booking->setStartTime(new DateTime('1970-01-01 00:00'));
                $booking->setEndTime(new DateTime('1970-01-01 00:00'));

                if (!$this->timeUnitIsDay) {
                    //Sync booking date and time from date and time range
                    $timeRange = clone $form->get('time_range')->getData();
                    $dateTimeRange = DateTimeRange::addTimesToDates($dateRange, $timeRange);
                    $booking->setDateRange($dateTimeRange->getDateRange());
                    $booking->setTimeRange($dateTimeRange->getFirstTimeRange());
                }

                $event->setData($booking);
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Booking',
                'csrf_token_id' => 'booking_price',
                'translation_domain' => 'cocorico_booking',
                'constraints' => new Valid(),
                'validation_groups' => array('new'),
//                'error_bubbling' => false,//To prevent errors bubbling up to the parent form
//                //To map errors of all unmapped properties (date_range) to a particular field (date_range)
//                'error_mapping' => array(
//                    '.' => 'date_range',
//                ),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'booking_price';
    }

}

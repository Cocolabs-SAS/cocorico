<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\TimeBundle\Form\DataTransformer;

use Cocorico\TimeBundle\Model\TimeRange;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TimeRangeViewTransformer implements DataTransformerInterface
{
    //Default timezone
    const VIEW_TIMEZONE = 'UTC';
    const MODEL_TIMEZONE = 'UTC';

    protected $options = array();

    public function __construct(OptionsResolver $resolver, array $options = array())
    {
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'timezone' => self::VIEW_TIMEZONE //view_timezone
            )
        );
    }

    /**
     * Transforms normalized data to view data.
     *
     * @param  TimeRange|null $value
     * @return string
     */
    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof TimeRange) {
            throw new UnexpectedTypeException($value, 'Cocorico\TimeBundle\Model\TimeRange');
        }

        return $value;
    }

    /**
     * Transforms view data to normalized data.
     *
     * @param  TimeRange|null $value
     * @return string
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof TimeRange) {
            throw new UnexpectedTypeException($value, 'Cocorico\TimeBundle\Model\TimeRange');
        }

        $value = $this->reverseTransformWithDST($value);

        return $value;
    }

    /**
     * TLDR: Add date to time to include DST in timezone offset
     *
     * Handle DST on TimeRange timezone time fields (start and end properties):
     *  - Reset TimeType DateTimeToArrayTransformer ViewTransformer
     *  - Set TimeRange date time fields to DateRange start date
     *  - Recompute TimeZone offset with DST
     *  - Reset TimeRange dates of TimeRange time fields to '1970-01-01'
     *
     * Ex: User is in NY Timezone and Server is in UTC
     *     User enters 2017-08-07 - 2017-08-08 in DateRange
     *     User enters 08:00 - 10:00 in TimeRange (NY timezone)
     *     The default TimeType field transformer reverseTransform times to 13:00 - 15:00 (UTC without DST)
     *     This transformer:
     *          reset previous transformation to original 08:00 - 10:00,
     *          set times dates to DateRange start date (2017-08-07),
     *          recompute times according to NY timezone with DST
     *
     * @param TimeRange|null $timeRange
     * @return TimeRange
     * @throws TransformationFailedException
     */
    private function reverseTransformWithDST(TimeRange $timeRange = null)
    {
        if ($timeRange && $timeRange->getStart() && $timeRange->getEnd()) {
            $fields = array('start', 'end');
            $viewTimezone = $this->options['timezone'];
            $date = $timeRange->getDate();

            try {
                foreach ($fields as $index => $field) {
                    $getter = 'get' . ucfirst($field);
                    /** @var \DateTime $time */
                    $time = $timeRange->$getter();
                    //reset timezone modification done by TimeType DateTimeToArrayTransformer
                    $time->setTimezone(new \DateTimeZone($viewTimezone));

                    //set times dates to DateRange dates and recompute times according to timezone with DST
                    $dateTime = new \DateTime(
                        sprintf(
                            '%s-%s-%s %s:%s:%s',
                            $date->format('Y'),
                            $date->format('m'),
                            $date->format('d'),
                            $time->format('H'),
                            $time->format('i'),
                            $time->format('s')
                        ),
                        new \DateTimeZone($viewTimezone)
                    );

                    if ($viewTimezone !== self::MODEL_TIMEZONE) {
                        $dateTime->setTimezone(new \DateTimeZone(self::MODEL_TIMEZONE));
                    }

                    $setter = 'set' . ucfirst($field);
                    $timeRange->$setter($dateTime);
                }

                //If start is greater than end then time range spans day and end is equal to next day
                if ($timeRange->getStart()->format('Y-m-d H:i') > $timeRange->getEnd()->format('Y-m-d H:i')) {
                    $timeRange->getEnd()->modify('+1 day');
                }
            } catch (\Exception $e) {
                throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $timeRange;
    }
}

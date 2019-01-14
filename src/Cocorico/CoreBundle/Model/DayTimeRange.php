<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model;


class DayTimeRange
{
    /**
     * @var \DateTime
     */
    public $day;

    /**
     * @var \DateTime[]
     */
    public $timeRanges;

    /**
     * DayTimeRange constructor.
     * @param \DateTime  $day
     * @param array|null $timeRanges
     */
    public function __construct(\DateTime $day, array $timeRanges = array())
    {
        $this->day = $day;
        $this->timeRanges = $timeRanges;
    }

    /**
     * @return \DateTime
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param \DateTime $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @return \DateTime[]
     */
    public function getTimeRanges()
    {
        return $this->timeRanges;
    }

    /**
     * @param \DateTime[] $timeRanges
     */
    public function setTimeRanges($timeRanges)
    {
        $this->timeRanges = $timeRanges;
    }

    /**
     * Check if multi days time ranges overlap
     *
     * @param DayTimeRange[] $daysTimeRangesA
     * @param DayTimeRange[] $daysTimeRangesB
     * @return bool
     */
    public static function overlap($daysTimeRangesA, $daysTimeRangesB)
    {
        foreach ($daysTimeRangesA as $dayTimeRangesA) {
            /** @var \DateTime $day */
            $day = $dayTimeRangesA->day;
            /** @var TimeRange $range */
            $range = reset($dayTimeRangesA->timeRanges);

            foreach ($daysTimeRangesB as $dayTimeRangesB) {
                /** @var \DateTime $dayToCheck */
                $dayToCheck = $dayTimeRangesB->day;
                /** @var TimeRange $rangeToCheck */
                $rangeToCheck = reset($dayTimeRangesB->timeRanges);
                if ($day->format('Ymd') == $dayToCheck->format('Ymd')) {
                    if ($rangeToCheck->overlap($range)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }


//    /**
//     * Get the first time range of a day
//     *
//     * @param \DateTime      $day
//     * @param DayTimeRange[] $daysTimeRanges
//     * @return TimeRange|bool
//     * @throws \Exception
//     */
//    public static function getTimeRangeByDay(\DateTime $day, array $daysTimeRanges)
//    {
//        $timeRange = false;
//
//        foreach ($daysTimeRanges as $dayTimeRanges) {
//            if ($dayTimeRanges->day->format('Ymd') == $day->format("Ymd")) {
//                $timeRange = reset($dayTimeRanges->timeRanges);
//                break;
//            }
//        }
//
//        if (!$timeRange) {
//            throw new \Exception('Missing time range');
//        }
//
//        return $timeRange;
//    }


}

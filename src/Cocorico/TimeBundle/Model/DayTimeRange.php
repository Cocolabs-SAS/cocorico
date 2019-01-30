<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\TimeBundle\Model;


class DayTimeRange
{
    /**
     * @var \DateTime
     */
    public $day;

    /**
     * @var TimeRange[]
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
     * @return TimeRange[]
     */
    public function getTimeRanges()
    {
        return $this->timeRanges;
    }

    /**
     * @param TimeRange[] $timeRanges
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

}

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


class DateRange
{
    /**
     * @var \DateTime
     */
    public $start;

    /**
     * @var \DateTime
     */
    public $end;

    /**
     * @var int
     */
    public $nbDays;

    public function __construct(\DateTime $start = null, \DateTime $end = null)
    {
        if (!$start) {
            $start = new \DateTime();
            $start->setTime(0, 0, 0);
        }

        if (!$end) {
            $end = new \DateTime();
            $end->setTime(0, 0, 0);
        }

        $this->start = $start;
        $this->end = $end;
        //$this->nbDays = $start->diff($end)->days;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return int
     */
    public function getNbDays()
    {
        return $this->nbDays;
    }

    /**
     * @param int $nbDays
     */
    public function setNbDays($nbDays)
    {
        $this->nbDays = $nbDays;
    }


    /**
     * @param bool $endDayIncluded
     * @return int
     */
    public function getDuration($endDayIncluded)
    {
        $duration = $this->getStart()->diff($this->getEnd());
        $duration = $duration->days;
        if ($endDayIncluded) {
            $duration = $duration + 1;
        }

        return $duration;
    }

    /**
     * Create DateRange instance from array
     *
     * @param array $dateR
     * @return null|DateRange
     */
    public static function createFromArray($dateR)
    {
        if (isset($dateR['start']) && isset($dateR['end']) && $dateR['start'] && $dateR['end']) {
            $start = \DateTime::createFromFormat('d/m/Y', $dateR['start']);
            $end = \DateTime::createFromFormat('d/m/Y', $dateR['end']);

            return new static($start, $end);
        }

        return null;
    }

    /**
     * Get time ranges for each day of this  date range.
     * For each If a time range overlap day (22h -> 02h) the time range is splitted in two time ranges :
     *  - first time range is from start time to midnight (1970-01-01 22h -> 1970-01-02 00h)
     *  - second one is from midnight to end time (1970-01-01 00h -> 1970-01-01 02h)
     *
     * @param TimeRange[] $timeRanges
     * @param bool $endDayIncluded
     * @param bool $timeUnitIsDay
     *
     * @return DayTimeRange[]
     */
    public function getTimeRangesByDay(array $timeRanges, $endDayIncluded, $timeUnitIsDay)
    {
        $ranges = array();

//        echo 'getTimeRanges' . '<br>';
        $start = clone $this->getStart();
//        echo $start->format('Y-m-d H:i') . '<br>';
        $start->setTime(0, 0, 0);
        $end = clone $this->getEnd();
        $end->setTime(0, 0, 0);

        if ($endDayIncluded) {
            $end->modify('+1 day');
        }
        //Add day in not day mode for eventual time range overlapping day
        if (!$timeUnitIsDay) {
            $end->modify('+1 day');
        }

//        echo $end->format('Y-m-d H:i') . '<br>';

        $interval = new \DateInterval('P1D');
        $days = new \DatePeriod($start, $interval, $end);
        /** @var \DateTime[] $days */
        $nbDays = iterator_count($days);
        foreach ($days as $i => $day) {
            $firstDay = ($i == 0);
            $lastDay = ($i == $nbDays - 1);

            $range = new DayTimeRange($day, array());

            foreach ($timeRanges as $j => $timeRange) {
                $startTime = $timeRange->getStart();
                $endTime = $timeRange->getEnd();

//                echo $startTime->format('Y-m-d H:i') . '<br>';
//                echo $endTime->format('Y-m-d H:i') . '<br>';
                //Split time range overlapping day into the two consecutive days
                if ($timeRange->overlapDays()) {
                    //First time range part is added to current day if it is not the last day of days ranges
                    if (!$lastDay) {
                        $range->timeRanges[] = new TimeRange($startTime, new \DateTime('1970-01-02 00:00:00'));
                    }

                    //Second time range part is added if it is not the first day or if it is the last day of day ranges
                    if (!$firstDay || $lastDay) {
                        $range->timeRanges[] = new TimeRange(
                            new \DateTime('1970-01-01 00:00:00'),
                            new \DateTime('1970-01-01 ' . $endTime->format('H:i:s'))
                        );
                    }
                } else {//Time range don't overlap day and is added as is.
                    if (!$lastDay) {
                        $range->timeRanges[] = $timeRange;
                    }
                }
            }

            //Add last range if day mode or if no day mode and not last day or last day with time range
            if ($timeUnitIsDay ||
                (!$timeUnitIsDay && (!$lastDay || ($lastDay && count($range->timeRanges))))
            ) {
                $ranges[$day->format('Ymd')] = $range;
            }
        }

//        echo print_r($ranges, 1 ) . '<br>';
//        die();

        return $ranges;
    }
}

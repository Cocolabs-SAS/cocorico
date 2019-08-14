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

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeZone;

/**
 * Class DateTimeRange
 *
 * Object representing a date range and its relative time ranges
 *
 * Its main goal is to return DayTimeRange array containing all time ranges for each days of date range
 */
class DateTimeRange
{
    /**
     * @var DateRange
     */
    public $dateRange;

    /**
     * @var TimeRange[]
     */
    public $timeRanges;

    /**
     * DateTimeRange constructor.
     * @param DateRange|null $dateRange
     * @param TimeRange[]    $timeRanges
     */
    public function __construct(DateRange $dateRange = null, $timeRanges = array())
    {
        $this->setDateRange($dateRange);
        $this->setTimeRanges($timeRanges);
    }

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @param DateTime|null $startTime
     * @param DateTime|null $endTime
     * @return static
     */
    public static function createFromDateTimes(
        DateTime $start = null,
        DateTime $end = null,
        DateTime $startTime = null,
        DateTime $endTime = null
    ) {
        $dateRange = new DateRange($start, $end);
        $timeRanges = array(new TimeRange($startTime, $endTime, $start));

        return new static($dateRange, $timeRanges);
    }

    /**
     * @return DateRange
     */
    public function getDateRange()
    {
        return $this->dateRange;
    }

    /**
     * @param DateRange $dateRange
     */
    public function setDateRange($dateRange)
    {
        $this->dateRange = $dateRange;
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
        if ($timeRanges && count($timeRanges)) {
            //Order descending
            usort(
                $timeRanges,
                function (TimeRange $first, TimeRange $second) {
                    return strcmp(
                        $first->getStart()->format('YmdHi') . '-' . $first->getEnd()->format('YmdHi'),
                        $second->start->format('YmdHi') . '-' . $second->getEnd()->format('YmdHi')
                    );
                }
            );
        }

        $this->timeRanges = $timeRanges;
    }

    /**
     * @return TimeRange|null
     */
    public function getFirstTimeRange()
    {
        return count($this->timeRanges) ? $this->timeRanges[0] : null;
    }


    /**
     * Get time ranges for each day of this date range.
     * For each date if a time range spans day (22h -> 02h) the time range is splitted in two time ranges on the two spanning days:
     *  - first time range is from start time to midnight (2017-10-13 22h -> 2017-10-14 00h)
     *  - second one is from midnight to end time (2017-10-14 00h -> 2017-10-14 02h)
     *
     * @param bool   $endDayIncluded
     * @param array  $weekDays
     * @param string $timezone
     *
     * @return DayTimeRange[]
     */
    public function getDaysTimeRanges($endDayIncluded, $weekDays = array(), $timezone = 'UTC')
    {
        $start = clone $this->getDateRange()->getStart();
        $start->setTime(0, 0, 0);
        $end = clone $this->getDateRange()->getEnd();
        $end->setTime(0, 0, 0);

        if ($endDayIncluded) {
            $end->modify('+1 day');
        }

        $firstDayTimeRange = array();
        if (count($this->getTimeRanges()) && $this->getFirstTimeRange()->getStart()) {
            $this->adjustStartAndEndDay($start, $end);
            $firstDayTimeRange = $this->getInitialTimeRanges($start);
        }

        $daysTimeRanges = $this->iterateInitialTimeRangesOnDays($start, $end, $firstDayTimeRange, $timezone);

        if (count($weekDays)) {
            $hasTimeRanges = count($firstDayTimeRange);
            $daysTimeRanges = $this->filterByWeekDays($daysTimeRanges, $weekDays, $timezone, $hasTimeRanges);
        }

        return $daysTimeRanges;

    }

    /**
     *  Adjustment of first and last day of date range depending on first smaller day of time ranges
     *
     * @param DateTime $start
     * @param DateTime $end
     */
    private function adjustStartAndEndDay(DateTime &$start, DateTime &$end)
    {
        //First day is equal to the first smaller day of time ranges
        $firstDay = clone $this->getFirstTimeRange()->getStart();
        $firstDay->setTime(0, 0, 0);

        //If the first smaller day of the time range is less than start date range day then end date range day is decremented by one.
        //This case occurs when first time range day is before DateRange start in UTC timezone
        if ($firstDay->format('Y-m-d H:i') < $start->format('Y-m-d H:i')) {
            $end->modify('-1 day');
        }

        $start = $firstDay;
    }

    /**
     * Return initial time ranges by day since first day
     *
     * @param DateTime $firstDay
     * @return array [offsetDay][nbMinutes] => array( 0 => firstStartMinute, 1 => secondStartMinute, ...)
     *               - offsetDay: number of day since $firstDay (ex: 1 => time range day is equal to $firstDay + 1 day)
     *               - nbMinutes: time range duration in minutes (ex: 60 => time range duration is 60 minutes)
     *               - firstStartMinute, ...: time range start at this minute in the day (ex: 120 => 02h00 AM)
     */
    private function getInitialTimeRanges(DateTime $firstDay)
    {
        $ranges = array();
        $nextDayTimeRange = null;
        foreach ($this->getTimeRanges() as $i => $timeRange) {
            $startTime = $timeRange->getStart();
            $endTime = $timeRange->getEnd();

            $day = clone $startTime;
            $day->setTime(0, 0, 0);
            $diffDay = $firstDay->diff($day)->days;//Number of days between first day and time range start time

            $startDay = clone $startTime;
            $startDay->setTime(0, 0, 0);
            if ($timeRange->overlapDays()) {
                $midnight = clone $startTime;
                $midnight->modify('+1 day');
                $midnight->setTime(0, 0, 0);

                //Time range start and duration
                $nbMinutes = ($midnight->getTimestamp() - $startTime->getTimestamp()) / 60;
                $startMinute = ($startTime->getTimestamp() - $startDay->getTimestamp()) / 60;
                $ranges[$diffDay][$nbMinutes][] = $startMinute;

                //Next day time range start and duration
                $nbMinutes = ($endTime->getTimestamp() - $midnight->getTimestamp()) / 60;
                $ranges[$diffDay + 1][$nbMinutes][] = 0;
            } else {
                //Time range start and duration
                $nbMinutes = ($endTime->getTimestamp() - $startTime->getTimestamp()) / 60;
                $startMinute = ($startTime->getTimestamp() - $startDay->getTimestamp()) / 60;
                $ranges[$diffDay][$nbMinutes][] = $startMinute;
            }
        }

        return $ranges;
    }

    /**
     * Return DayTimeRange objects for all time ranges of all days of this date range and initial time range
     *
     * @param DateTime $start
     * @param DateTime $end
     * @param  array   $initialTimeRanges see getInitialTimeRanges
     * @param  string  $timezone
     *
     * @return array with date string as index
     */
    private function iterateInitialTimeRangesOnDays($start, $end, $initialTimeRanges, $timezone)
    {
//        echo '<pre>';
        $hasTimeRanges = count($initialTimeRanges);
        $dstTransitions = $this->getDSTTransitions($start, $end, $timezone);

        $interval = new DateInterval('P1D');
        $days = new DatePeriod($start, $interval, $end);
        $nbDays = max(iterator_count($days), 1);

        /** @var DayTimeRange[] $daysTimeRanges */
        $daysTimeRanges = array();
        $newOffset = 0;
        for ($d = 0; $d < $nbDays; $d++) {
            $day = clone $start;
            $day->modify('+' . $d . ' day');
            $day->setTime(0, 0, 0);

            if ($hasTimeRanges) {
                $this->iterateInitialTimeRangesOnDay(
                    $day,
                    $initialTimeRanges,
                    $dstTransitions,
                    $newOffset,
                    $daysTimeRanges
                );
            } else {
                $newDay = clone $day;
                $startDayString = $day->format('Y-m-d');
                $daysTimeRanges[$startDayString] = new DayTimeRange($newDay, array());
            }
        }
//        print_r($daysTimeRanges);
//        die();

        return $daysTimeRanges;
    }

    /**
     * Iterate initial time range on a day
     *
     * @param DateTime              $day
     * @param                       $initialTimeRanges
     * @param  array                $dstTransitions
     * @param  int                  $newOffset      DST offset in minutes
     * @param  DayTimeRange[]       $daysTimeRanges result
     */
    private function iterateInitialTimeRangesOnDay(
        DateTime $day,
        $initialTimeRanges,
        &$dstTransitions,
        &$newOffset,
        &$daysTimeRanges
    ) {
        foreach ($initialTimeRanges as $diffDay => $timeRanges) {
            /** @var TimeRange $timeRange */
            foreach ($timeRanges as $nbMinutes => $startMinutes) {
                foreach ($startMinutes as $startMinute) {
//                        echo $newOffset . '<br>';
                    $startMinute += $newOffset;//Add/remove DST offset if any DST transition occurs in time ranges
                    $startMinute = $startMinute >= 0 ? '+ ' . $startMinute . ' minute' : $startMinute . ' minute';
                    //Start time
                    $startTime = clone $day;
                    $startTime->modify('+' . $diffDay . ' day');
                    $startTime->modify($startMinute);
                    $offset = $this->getDSTOffset($startTime, $dstTransitions);
                    if ($offset) {
                        $offsetString = $offset >= 0 ? '+ ' . $offset . ' minute' : $offset . ' minute';
                        $startTime->modify($offsetString);
                        $newOffset = $newOffset + $offset;
                    }

                    //End time
                    $endTime = clone $startTime;
                    $endTime->modify('+ ' . $nbMinutes . ' minute');
                    $offset = $this->getDSTOffset($endTime, $dstTransitions);
                    if ($offset) {
                        $offsetString = $offset >= 0 ? '+ ' . $offset . ' minute' : $offset . ' minute';
                        $endTime->modify($offsetString);
                        $newOffset = $newOffset + $offset;
                        //todo: check multiple DST transitions in the date range
                    }

                    $this->addDayTimeRange($startTime, $endTime, $daysTimeRanges);
//                        echo $startTime->format('Y-m-d H:i') . ' / ' . $endTime->format('Y-m-d H:i') . '<br>';
                }
            }
        }
    }

    /**
     * @param DateTime        $startTime
     * @param DateTime        $endTime
     * @param  DayTimeRange[] $daysTimeRanges result
     */
    private function addDayTimeRange(DateTime $startTime, DateTime $endTime, &$daysTimeRanges)
    {
        $startDayString = $startTime->format('Y-m-d');
        $endDayString = $endTime->format('Y-m-d');
        /** @var DateTime[][] $newTimeRanges */
        $newTimeRanges = array($startDayString => array($startTime, $endTime));
        //If endTime is tomorrow then time range is splitted on spanning days
        if ($endDayString != $startDayString && $endTime->format('H:i') != '00:00') {
            $midnight = clone $endTime;
            $midnight->setTime(0, 0, 0);

            $newTimeRanges[$startDayString][1] = $midnight;
            $newTimeRanges[$endDayString] = array($midnight, $endTime);
        }

        //Fill $daysTimeRanges day by day and time range by time range
        foreach ($newTimeRanges as $dayString => $newTimeRange) {
            if (isset($daysTimeRanges[$dayString])) {
                $dayTimeRange = $daysTimeRanges[$dayString];
            } else {
                $newDay = clone $newTimeRange[0];
                $newDay->setTime(0, 0, 0);
                $dayTimeRange = new DayTimeRange($newDay, array());
            }

//                            echo $newTimeRange[0]->format('Y-m-d H:i') . ' / ' . $newTimeRange[1]->format('Y-m-d H:i') . '<br>';

            $dayTimeRange->timeRanges[] = new TimeRange($newTimeRange[0], $newTimeRange[1]);
            $daysTimeRanges[$dayString] = $dayTimeRange;
        }
    }

    /**
     * Return DST offset if DST transition occurs before $time.
     * If DST transition occurs then it is removed from DSTs to apply.
     * As time ranges are checked time after time DST offset has to be apply only one time.
     *
     * Ex: With DST change at 2017-10-29 01:00 in Paris timezone (UTC +2)
     *
     * @param DateTime $time
     * @param array    $dstTransitions decreasing ordered
     * @return int DST offset in minutes
     */
    private function getDSTOffset($time, &$dstTransitions)
    {
        $dstOffset = 0;
        foreach ($dstTransitions as $dstDateString => $offset) {
            if ($time->format('Y-m-d H:i') >= $dstDateString) {// First start range impacted by DST
                $dstOffset = $offset * 60;
                unset($dstTransitions[$dstDateString]);
                break;
            }
        }

        return $dstOffset;
    }


    /**
     * Get date and offset in hour for DST transitions between start and end date
     *
     * @param DateTime $start
     * @param DateTime $end
     * @param  string  $timezone
     *
     * @return array
     */
    private function getDSTTransitions(DateTime $start, DateTime $end, $timezone)
    {
        //We only need DST transitions from first lowest date time so if fist time range exists
        // then start is equal to start time
        $start = clone $start;
        $start = $this->getFirstTimeRange() && $this->getFirstTimeRange()->getStart() ?
            $this->getFirstTimeRange()->getStart() : $start;

        $tz = new DateTimeZone($timezone);
        $transitions = $tz->getTransitions($start->getTimestamp(), $end->getTimestamp());

        $dstTransitions = array();
        if (count($transitions) > 1) {
            foreach ($transitions as $i => $transition) {
                if ($i == 0) {
                    continue;
                }
                $date = new DateTime();
                $date->setTimestamp($transition['ts']);
                $offset = round(($transitions[$i - 1]['offset'] - $transition['offset']) / 3600);
                $dstTransitions[$date->format('Y-m-d H:i')] = $offset;
            }
        }

        arsort($dstTransitions);

//        print_r($dstTransitions);
//        die();

        return $dstTransitions;
    }


    /**
     * Filter DayTimeRanges by week days
     *
     * @param DayTimeRange[] $daysTimeRanges $daysTimeRanges
     * @param array          $weekDays
     * @param string         $timezone
     * @param bool           $hasTimeRanges
     *
     * @return DayTimeRange[]
     */
    private function filterByWeekDays($daysTimeRanges, $weekDays, $timezone, $hasTimeRanges)
    {
        $prevEnd = $added = false;
        foreach ($daysTimeRanges as $day => $dayTimeRanges) {
            if (!$hasTimeRanges) {
                $isInWeekDays = in_array($dayTimeRanges->getDay()->format('N'), $weekDays);
                if (!$isInWeekDays) {
                    unset($daysTimeRanges[$day]);
                }
            } else {
                foreach ($dayTimeRanges->getTimeRanges() as $index => $timeRange) {
                    $start = clone $timeRange->getStart();
                    $end = clone $timeRange->getEnd();
                    $start->setTimezone(new DateTimeZone($timezone));
                    $end->setTimezone(new DateTimeZone($timezone));

                    $isInWeekDays = in_array($start->format('N'), $weekDays) &&
                        (in_array($end->format('N'), $weekDays) || $end->format('H:i') == '00:00');
                    if ((!$prevEnd && $isInWeekDays) ||
                        ($prevEnd && $isInWeekDays && ($start != $prevEnd || $start->format(
                                    'H:i'
                                ) == '00:00' || $added))) {
//                        echo 'added';
                        $added = true;
                    } else {
                        unset($daysTimeRanges[$day]->timeRanges[$index]);
//                        echo 'not added';
                        $added = false;
                    }
                    $prevEnd = $end;
                }

                if (!count($daysTimeRanges[$day]->getTimeRanges())) {
                    unset($daysTimeRanges[$day]);
                }
            }

        }

        return $daysTimeRanges;
    }

    /**
     * Add time range times to date range dates
     *
     * Start date have time equal to start time
     * End date have time equal to end time
     *
     * @param DateRange $dateRange
     * @param TimeRange $timeRange
     *
     * @return DateTimeRange
     */
    public static function addTimesToDates(DateRange $dateRange = null, TimeRange $timeRange = null)
    {
        if ($dateRange && $timeRange) {
            if ($timeRange->getStart() && $timeRange->getEnd()) {
                $nbDays = $dateRange->getStart()->diff($dateRange->getEnd())->days;

                $dateRange->setStart(clone $timeRange->getStart());
                $dateRange->setEnd(clone $timeRange->getEnd());
                $dateRange->getEnd()->modify('+' . $nbDays . ' day');
            }
        }

        return new static($dateRange, array($timeRange));
    }

}

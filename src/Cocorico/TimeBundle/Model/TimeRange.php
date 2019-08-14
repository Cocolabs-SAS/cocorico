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
use DateTime;

class TimeRange
{
    /**
     * @var DateTime
     */
    public $start;

    /**
     * @var DateTime
     */
    public $end;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var int
     */
    public $nbMinutes;

    /**
     * TimeRange constructor.
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @param DateTime|null $date
     */
    public function __construct(DateTime $start = null, DateTime $end = null, DateTime $date = null)
    {
        if (!$start) {
            $start = new DateTime("1970-01-01 00:00:00");
        }

        if (!$end) {
            $end = new DateTime("1970-01-01 23:59:59");
        }

        if (!$date) {
            $date = new DateTime();
            $date->setTime(0, 0, 0);
        }

        $this->date = $date;

        //if start time is greater than end time then end time correspond to the next day of start day
        if ($start->getTimestamp() > $end->getTimestamp()) {
            $end->add(new DateInterval('P1D'));
        }

        $this->start = $start;
        $this->end = $end;
        $this->nbMinutes = abs($end->getTimestamp() - $start->getTimestamp()) / 60;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param DateTime $start
     */
    public function setStart($start)
    {
        $this->start = $start;

        //Update nb minutes if start is changed
        if ($this->start && $this->end) {
            $this->nbMinutes = abs($this->end->getTimestamp() - $this->start->getTimestamp()) / 60;
        }
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
        //Update nb minutes if end is changed
        if ($this->start && $this->end) {
            $this->nbMinutes = abs($this->end->getTimestamp() - $this->start->getTimestamp()) / 60;
        }
    }

    /**
     * @return int
     */
    public function getNbMinutes()
    {
        return $this->nbMinutes;
    }

    /**
     * @param int $nbMinutes
     */
    public function setNbMinutes($nbMinutes)
    {
        $this->nbMinutes = $nbMinutes;
    }


    /**
     * Return start minute since midnight
     *
     * @return int
     */
    public function getStartMinute()
    {
        $midnight = clone $this->getStart();
        $midnight->setTime(0, 0, 0);

        return intval(($this->getStart()->getTimestamp() - $midnight->getTimestamp()) / 60);
    }

    /**
     * Return end minute in the day
     *
     * @return int
     */
    public function getEndMinute()
    {
        $midnight = clone $this->getEnd();
        $midnight->setTime(0, 0, 0);

        $endMinute = intval(($this->getEnd()->getTimestamp() - $midnight->getTimestamp()) / 60);
        if ($this->getEnd()->format('H:i') == '00:00') {
            $endMinute = 1440;
        }

        return $endMinute;

    }

    /**
     * @param int $timeUnit
     * @return int number of times unit
     */
    public function getDuration($timeUnit)
    {
        $duration = ($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp()) / 60 / $timeUnit;

        return max($duration, 0);
    }


    /**
     * Check if a time range is overlapping two consecutive days (22h -> 02h)
     *
     * Ex: 1970-01-01 22h -> 1970-01-01 01h OR 1970-01-01 22h -> 1970-01-02 01h
     *
     * @return bool
     */
    public function overlapDays()
    {
        $start = $this->getStart();
        $end = $this->getEnd();

        if ($end->format('H:i') != '00:00' &&
            ($start->getTimestamp() > $end->getTimestamp() || $start->format('Ymd') != $end->format('Ymd'))
        ) {

            return true;
        }

        return false;
    }

    /**
     * Check if this time ranges overlap with an other
     *
     * @param TimeRange $timeRange
     * @return bool
     */
    public function overlap(TimeRange $timeRange)
    {
        if (
            $this->getStart()->getTimestamp() < $timeRange->getEnd()->getTimestamp() &&
            $this->getEnd()->getTimestamp() > $timeRange->getStart()->getTimestamp()
        ) {
            return true;
        }

        return false;
    }


    public function log($prefix = '')
    {
        echo "TimeRange";
        if ($prefix) {
            echo "<br>$prefix";
        }
        if ($this->getStart() && $this->getEnd()) {
            echo $this->getStart()->format('Y-m-d H:i') . ' / ' . $this->getEnd()->format('Y-m-d H:i') . '<br>';
        }
    }
}

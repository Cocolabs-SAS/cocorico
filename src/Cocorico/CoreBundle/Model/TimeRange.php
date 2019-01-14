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


class TimeRange
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
    public $nbMinutes;

    public function __construct(\DateTime $start = null, \DateTime $end = null)
    {
        if (!$start) {
            $start = new \DateTime("1970-01-01 00:00:00");
        }

        if (!$end) {
            $end = new \DateTime("1970-01-01 23:59:59");
        }

        //if start time is greater than end time then end time correspond to the next day of start day
        if ($start->getTimestamp() > $end->getTimestamp()) {
            $end->add(new \DateInterval('P1D'));
        }

        $this->start = $start;
        $this->end = $end;
        $this->nbMinutes = abs($end->getTimestamp() - $start->getTimestamp()) / 60;
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

        //Update nb minutes if start is changed
        $this->nbMinutes = abs($this->end->getTimestamp() - $this->start->getTimestamp()) / 60;
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
        //Update nb minutes if end is changed
        $this->nbMinutes = abs($this->end->getTimestamp() - $this->start->getTimestamp()) / 60;
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
     * @param int $timeUnit
     * @return int number of times unit
     */
    public function getDuration($timeUnit)
    {
//        echo $this->getStart()->format('Y-m-d H:i') . '<br>';
//        echo $this->getEnd()->format('Y-m-d H:i') . '<br>';

        if ($this->getEnd()->format('H:i') == '00:00') {//End minute is equal to 1440*60=86400 and not 0
            $duration = (86400 - $this->getStart()->getTimestamp()) / 60;
        } else {
            $duration = ($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp()) / 60;
        }

//        echo $duration . '<br>';
//        die();
        $duration = $duration / $timeUnit;

        return max($duration, 0);
    }

    /**
     * Create TimeRange instance from array
     *
     * @param array $timeR
     * @return null|TimeRange
     */
    public static function createFromArray($timeR)
    {
        if (isset($timeR['start']) && isset($timeR['end']) &&
            is_numeric($timeR['start']['hour']) && is_numeric($timeR['end']['hour']) &&
            is_numeric($timeR['start']['minute']) && is_numeric($timeR['end']['minute'])
        ) {
            $start = new \DateTime("1970-01-01 " . $timeR['start']['hour'] . ":" . $timeR['start']['minute']);
            $end = new \DateTime("1970-01-01 " . $timeR['end']['hour'] . ":" . $timeR['end']['minute']);

            if ($start->getTimestamp() > $end->getTimestamp()) {
                $end->add(new \DateInterval('P1D'));
            }

            return new static($start, $end);
        }

        return null;
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

    /**
     * Return start minute in the day
     *
     * @return int
     */
    public function getStartMinute()
    {
        return intval($this->getStart()->getTimestamp() / 60);
    }

    /**
     * Return end minute in the day
     *
     * @return int
     */
    public function getEndMinute()
    {
        $endMinute = $this->getStartMinute() + $this->getNbMinutes();
        if ($this->getEnd()->format('H:i') == '00:00') {
            $endMinute = 1440;
        }

        return $endMinute;

    }
}

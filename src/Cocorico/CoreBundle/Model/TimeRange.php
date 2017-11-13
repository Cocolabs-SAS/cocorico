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
        if ($this->getEnd()->format('H:i') == '00:00') {//End minute is equal to 1440*60=86400 and not 0
            $duration = (86400 - $this->getStart()->getTimestamp()) / 60;
        } else {
            $duration = ($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp()) / 60;
        }

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
            return new static(
                new \DateTime("1970-01-01 " . $timeR['start']['hour'] . ":" . $timeR['start']['minute']),
                new \DateTime("1970-01-01 " . $timeR['end']['hour'] . ":" . $timeR['end']['minute'])
            );
        }

        return null;
    }
}

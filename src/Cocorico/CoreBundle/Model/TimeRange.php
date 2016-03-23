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
        $duration = ($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp()) / 60;
        $duration = $duration / $timeUnit;

        return $duration;
    }
}

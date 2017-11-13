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

use DateTime;

class DateRange
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
     * @var int
     */
    public $nbDays;

    public function __construct(DateTime $start = null, DateTime $end = null)
    {
        if (!$start) {
            $start = new DateTime();
            $start->setTime(0, 0, 0);
        }

        if (!$end) {
            $end = new DateTime();
            $end->setTime(0, 0, 0);
        }

        $this->start = $start;
        $this->end = $end;
        //$this->nbDays = $start->diff($end)->days;
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

}

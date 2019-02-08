<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model\Manager;

use Cocorico\CoreBundle\Document\ListingAvailability;
use Cocorico\CoreBundle\Document\ListingAvailabilityTime;
use Cocorico\TimeBundle\Model\TimeRange;
use Doctrine\ODM\MongoDB\DocumentManager;

class ListingAvailabilityTimeManager
{
    protected $dm;
    protected $timeUnit;
    protected $timeUnitIsDay;

    /**
     * @param DocumentManager $dm
     * @param int             $timeUnit
     */
    public function __construct(DocumentManager $dm, $timeUnit)
    {
        $this->dm = $dm;
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
    }


    /**
     * Merge existing and new times. The result will replace all existing embedded ListingAvailabilityTime embed documents
     * for this day and this listing.
     *
     * @param array        $availability
     * @param TimeRange [] $timeRanges times don't overlap days (accept for example 02h->22h but not 22h->02h)
     * @param              string      (price|status) $typeModification
     * @param int          $defaultPrice
     * @param bool         $bookingCancellation
     *
     * @return ListingAvailabilityTime[]
     */
    public function mergeAvailabilityTimes(
        $availability,
        array $timeRanges,
        $typeModification,
        $defaultPrice,
        $bookingCancellation
    ) {
        /** @var \MongoDate $dayMD */
        $dayMD = $availability['d'];
        $day = new \DateTime();
        $day->setTimestamp($dayMD->sec);

        $times = array();
        if (isset($availability['ts'])) {
            foreach ($availability['ts'] as $l => $existingTime) {
                $times[intval($existingTime['_id'])] = $existingTime;
            }
        }

        //Get new times
        $status = $availability['s'];
        $price = $availability['p'];
        foreach ($timeRanges as $j => $timeRange) {
            //Replace existing minutes with new ones and add new ones if they don't exist
            for ($k = $timeRange->getStartMinute(); $k < $timeRange->getEndMinute(); $k++) {
                if (isset($times[$k])) {
                    $time = $times[$k];
                } else {
                    $time = array(
                        '_id' => null,
                        's' => null,
                        'p' => null,
                    );
                }

                if ($typeModification == 'status') {
                    $time = $this->setAvailabilityTimeStatus($time, $status, $defaultPrice, $bookingCancellation);
                } else {
                    $time = $this->setAvailabilityTimePrice($time, $price);
                }
                //For new time
                $time['_id'] = $k;

                $times[$k] = $time;
            }
        }
        ksort($times);

        $newTimes = array();
        foreach ($times as $k => $time) {
            $newTimes[] = $time;
        }

        return $newTimes;
    }

    /**
     * Set availabilities Status
     *
     * @param array $availabilityTime
     * @param int   $status
     * @param int   $defaultPrice
     * @param bool  $bookingCancellation
     *
     * @return array
     */
    private function setAvailabilityTimeStatus($availabilityTime, $status, $defaultPrice, $bookingCancellation)
    {
        //Status modification allowed only when listing availability time is not booked or when a booking is cancelled
        if ($availabilityTime["s"] != ListingAvailabilityTime::STATUS_BOOKED || $bookingCancellation) {
            $availabilityTime["s"] = $status;
        }

        if (!isset($availabilityTime["_id"]) || (!$availabilityTime["_id"] && $availabilityTime["_id"] !== 0)) {
            $availabilityTime["p"] = intval($defaultPrice);
        }

        return $availabilityTime;
    }

    /**
     * Set availabilities Price
     *
     * @param array $availabilityTime
     * @param int   $price
     *
     * @return array
     */
    private function setAvailabilityTimePrice($availabilityTime, $price)
    {
        //Price modification allowed only when listing is not booked
        if ($availabilityTime["s"] != ListingAvailabilityTime::STATUS_BOOKED) {
            $availabilityTime["p"] = intval($price);
        }

        if (!isset($availabilityTime["_id"]) || (!$availabilityTime["_id"] && $availabilityTime["_id"] !== 0)) {
            $availabilityTime["s"] = ListingAvailability::STATUS_AVAILABLE;
        }

        return $availabilityTime;
    }

    /**
     * Construct time ranges from ListingAvailabilityTimes
     *
     * @param array $availability
     * @param int   $addOneMinuteToEndTime 1 or 0
     * @param bool  $timeAsString
     *
     * @return array
     */
    public function getTimesRanges($availability, $addOneMinuteToEndTime = 1, $timeAsString = true)
    {
        $times = isset($availability["ts"]) ? $availability["ts"] : array();
        $timesRanges = $range = array();
        $prevStatus = $prevId = $prevPrice = false;

        foreach ($times as $i => $time) {
            if ($time["s"] !== $prevStatus || $time["_id"] != ($prevId + 1) || $time["p"] !== $prevPrice) {
                if ($prevStatus !== false && $prevId !== false) {
                    $end = $prevId + $addOneMinuteToEndTime;
                    if ($timeAsString) {
                        $end = date('H:i', mktime(0, $end));
                    }
                    $range['end'] = $end;
                    $timesRanges[] = $range;
                    //$range = array();
                }

                $start = $time["_id"];
                if ($timeAsString) {
                    $start = date('H:i', mktime(0, $start));
                }
                $range = array(
                    'start' => $start,
                    'status' => $time["s"],
                    'price' => $time["p"]
                );
            }

            $prevStatus = $time["s"];
            $prevPrice = $time["p"];
            $prevId = $time["_id"];
        }

        if (count($times)) {
            $lastTime = end($times);
            $end = $lastTime["_id"] + $addOneMinuteToEndTime;
            if ($timeAsString) {
                $end = date('H:i', mktime(0, $end));
            }
            $range['end'] = $end;
            $timesRanges[] = $range;
        }

        return $timesRanges;
    }

}

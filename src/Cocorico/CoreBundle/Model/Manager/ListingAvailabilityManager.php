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
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\CoreBundle\Repository\ListingAvailabilityRepository;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;

class ListingAvailabilityManager
{
    protected $dm;
    protected $timeManager;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $defaultListingStatus;
    protected $collection;
    protected $endDayIncluded;

    /**
     * @param DocumentManager                $dm
     * @param ListingAvailabilityTimeManager $timeManager
     * @param int                            $timeUnit
     * @param bool                           $defaultListingStatus
     * @param bool                           $endDayIncluded
     */
    public function __construct(
        DocumentManager $dm,
        ListingAvailabilityTimeManager $timeManager,
        $timeUnit,
        $defaultListingStatus,
        $endDayIncluded
    ) {
        $this->dm = $dm;
        $this->timeManager = $timeManager;
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->defaultListingStatus = $defaultListingStatus;
        $this->collection = $this->dm->getDocumentCollection('CocoricoCoreBundle:ListingAvailability');
        $this->endDayIncluded = $endDayIncluded;
    }

    /**
     * Save availabilities Status
     *
     * @param int          $listingId
     * @param DateRange    $dateRange
     * @param array        $weekDays
     * @param TimeRange [] $timeRanges
     * @param int|null     $status              ListingAvailability::$statusValues
     * @param int|null     $defaultPrice
     *                                          param bool         $endDayIncluded      For bookings it's equal to app endDayIncluded parameter else (status edition)
     *                                          it's false
     * @param bool         $bookingCancellation Booked status can be only modified when booking is canceled
     *
     * @throws \Exception
     */
    public function saveAvailabilitiesStatus(
        $listingId,
        DateRange $dateRange,
        array $weekDays,
        array $timeRanges,
        $status,
        $defaultPrice,
//        $endDayIncluded,
        $bookingCancellation
    ) {
        $this->saveAvailabilities(
            $listingId,
            $dateRange,
            $weekDays,
            $timeRanges,
            $status,
            null,
            $defaultPrice,
//            $endDayIncluded,
            $bookingCancellation
        );
    }

    /**
     * Save availabilities Prices
     *
     * @param int          $listingId
     * @param DateRange    $dateRange
     * @param array        $weekDays
     * @param TimeRange [] $timeRanges
     * @param int          $price
     *                                          param bool         $endDayIncluded      For bookings it's equal to app endDayIncluded parameter else (status edition)
     *                                          it's false
     * @param bool         $bookingCancellation Booked status can be only modified when booking is canceled
     *
     * @throws \Exception
     */
    public function saveAvailabilitiesPrices(
        $listingId,
        DateRange $dateRange,
        array $weekDays,
        array $timeRanges,
        $price,
//        $endDayIncluded,
        $bookingCancellation
    ) {
        $this->saveAvailabilities(
            $listingId,
            $dateRange,
            $weekDays,
            $timeRanges,
            null,
            $price,
            null,
//            $endDayIncluded,
            $bookingCancellation
        );
    }

    /**
     * Save availabilities status or price depending on status or price value
     *
     * @param int          $listingId
     * @param DateRange    $dateRange
     * @param array        $weekDays
     * @param TimeRange [] $timeRanges
     * @param int|null     $status              ListingAvailability::$statusValues
     * @param int|null     $price
     * @param int|null     $defaultPrice
     *                                          param bool         $endDayIncluded      For bookings it's equal to app endDayIncluded parameter else (status edition)
     *                                          it's false
     * @param bool         $bookingCancellation Booked status can be only modified when booking is canceled
     *
     * @throws \Exception
     */
    public function saveAvailabilities(
        $listingId,
        DateRange $dateRange,
        array $weekDays,
        array $timeRanges,
        $status,
        $price,
        $defaultPrice = null,
//        $endDayIncluded,
        $bookingCancellation
    ) {
        //Get time range by days
        $daysTimeRanges = $dateRange->getTimeRangesByDay($timeRanges, $this->endDayIncluded, $this->timeUnitIsDay);
//        print_r($ranges);
//        die();
        foreach ($daysTimeRanges as $i => $dayTimeRanges) {
            $this->saveAvailability(
                $listingId,
                $dayTimeRanges->day,
                $weekDays,
                $dayTimeRanges->timeRanges,
                $status,
                $price,
                $defaultPrice,
                $bookingCancellation
            );
        }
    }

    /**
     * Save availability status or price depending on status or price value
     *
     * @param           $listingId
     * @param \DateTime $day
     * @param array     $weekDays
     * @param array     $timeRanges
     * @param           $status
     * @param           $price
     * @param null      $defaultPrice
     * @param           $bookingCancellation
     */
    private function saveAvailability(
        $listingId,
        \DateTime $day,
        array $weekDays,
        array $timeRanges,
        $status,
        $price,
        $defaultPrice = null,
        $bookingCancellation
    ) {
        $typeModification = $status ? "status" : "price";

        //Is day in weekdays
        if (!count($weekDays) || in_array($day->format('N'), $weekDays)) {
            $availability = $this->getAvailability($listingId, $day);

            //No modification of booked availability for time unit day mode except when a booking is cancelled
            if (!$bookingCancellation) {
                if ($this->timeUnitIsDay && $availability["s"] == ListingAvailability::STATUS_BOOKED) {
                    return;
                }
            }

            if ($typeModification == "status") {
                $availability = $this->setAvailabilityStatus($availability, $status, $defaultPrice);
            } else {
                $availability = $this->setAvailabilityPrice($availability, $price);
            }

            $times = $this->timeManager->mergeAvailabilityTimes(
                $availability,
                $timeRanges,
                $typeModification,
                $defaultPrice,
                $bookingCancellation
            );

            //No time range means all day
            if (!count($timeRanges)) {
                $availability["ts"] = array();
            } else {
                $availability["ts"] = $times;
            }

                $this->collection->save($availability);
            }
        }

    /**
     * Get availability by listingId and day
     *
     * @param int       $listingId
     * @param \DateTime $day
     *
     * @return array
     */
    private function getAvailability($listingId, $day)
    {
        $dayP1 = new \DateTime($day->format("Y-m-d"));
        $dayP1->add(new \DateInterval('P1D'));

        //Get availability for this day if exist
        $existingAvailability = $this->getAvailabilitiesByListingAndDateRange(
            $listingId,
            $day,
            $dayP1
        );

        /** @var array $availability */
        if ($existingAvailability->count()) {
            $availability = $existingAvailability->getSingleResult();
        } else {
            $availability = array();
            $availability["lId"] = $listingId;
            $availability["d"] = new \MongoDate($day->getTimestamp());
            $availability["s"] = null;
        }

        return $availability;
    }

    /**
     * Set availabilities Status
     *
     * @param     $availability
     * @param int $status
     * @param int $defaultPrice
     *
     * @return array
     */
    public function setAvailabilityStatus($availability, $status, $defaultPrice)
    {
        $availability["s"] = $status;
        if (!isset($availability["_id"]) || !$availability["_id"]) {
            $availability["p"] = intval($defaultPrice);
        }

        return $availability;
    }

    /**
     * Set availabilities Price
     *
     * @param     $availability
     * @param int $price
     *
     * @return array
     */
    public function setAvailabilityPrice($availability, $price)
    {
        $availability["p"] = intval($price);
        if (!isset($availability["_id"]) || !$availability["_id"]) {
            $availability["s"] = ListingAvailability::STATUS_AVAILABLE;
        }

        return $availability;
    }


    /**
     * Merge existing and new times. The result will replace all existing embedded ListingAvailabilityTime embed documents
     * for this day and this listing.
     *
     * @param  array $availability
     * @param TimeRange []           $timeRanges
     * @param string  (price|status) $typeModification
     * @param int                    $defaultPrice
     * @param bool                   $bookingCancellation
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

        $times = array();
        if (isset($availability["ts"])) {
            foreach ($availability["ts"] as $l => $existingTime) {
                $times[intval($existingTime["_id"])] = $existingTime;
            }
        }

        //Get new times
        $status = $availability["s"];
        $price = $availability["p"];
        foreach ($timeRanges as $j => $timeRange) {
            /** @var \DateTime $startTime H:i */
            $startTime = $timeRange->getStart();
            $endTime = $timeRange->getEnd();

            //The start minute number
            $startMinute = intval($startTime->getTimestamp() / 60);
            $endMinute = intval($endTime->getTimestamp() / 60);
            if ($endTime->format('H:i') == '00:00') {
                $endMinute = 1440;
            }
            //Replace existing minutes with new ones and add new ones if they don't exist
            for ($k = $startMinute; $k < $endMinute; $k++) {
                if (isset($times[$k])) {
                    $time = $times[$k];
                } else {
                    $time = array(
                        "_id" => null,
                        "s" => null,
                        "p" => null,
                    );
                }

                if ($typeModification == "status") {
                    $time = $this->setAvailabilityTimeStatus($time, $status, $defaultPrice, $bookingCancellation);
                } else {
                    $time = $this->setAvailabilityTimePrice($time, $price);
                }
                //For new time
                $time["_id"] = $k;

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
    public function setAvailabilityTimeStatus($availabilityTime, $status, $defaultPrice, $bookingCancellation)
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
    public function setAvailabilityTimePrice($availabilityTime, $price)
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
     * Save availability times for one availability
     *
     * @param array $availability
     * @param                        $startTime
     * @param                        $endTime
     * @param string  (price|status) $typeModification
     * @param int                    $defaultPrice
     */

    public function saveAvailabilityTimes(
        $availability,
        $startTime,
        $endTime,
        $typeModification,
        $defaultPrice = null
    ) {
        $timeRange = array();
        if (!$this->timeUnitIsDay) {
            $start = new \DateTime('01-01-1970 ' . $startTime);
            $end = new \DateTime('01-01-1970 ' . $endTime);
            $end->add(new \DateInterval('PT1M'));

            $timeRange = array(0 => new TimeRange($start, $end));
        }

        $times = $this->mergeAvailabilityTimes(
            $availability,
            $timeRange,
            $typeModification,
            $defaultPrice,
            false
        );

        $availability["ts"] = $times;

        $this->collection->save($availability);
    }

    /**
     * Save availability
     *
     * @param ListingAvailability $availability
     *
     */
    public function saveAvailability(ListingAvailability $availability)
    {
        $this->dm->persist($availability);
        $this->dm->flush();
    }


    /**
     * Get ListingAvailability of a listing by date range
     *
     * @param int       $listingId
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string    $format calendar
     * @param boolean   $endDayIncluded
     *
     * @return array|Cursor|ListingAvailability[]
     */
    public function getAvailabilitiesByListingAndDateRange(
        $listingId,
        \DateTime $start,
        \DateTime $end,
        $format = "",
        $endDayIncluded = false
    ) {
        $events = array();
        /** @var ListingAvailability[] $availabilities */
        $availabilities = $this->getRepository()->findAvailabilitiesByListing(
            $listingId,
            $start,
            $end,
            $endDayIncluded,
            false
        );

        switch ($format) {
            case 'calendar' :
                foreach ($availabilities as $availability) {
                    $events = array_merge($events, $this->asCalendarEvent($availability));
                }
                break;
            case 'status':
                foreach ($availabilities as $availability) {
                    $events = $events + $this->asStatus($availability);
                }
                break;
            default:
                $events = $availabilities;
                break;
        }


        return $events;
    }

    /**
     * Get ListingAvailability as status raw
     *
     * @param ListingAvailability $availability
     *
     * @return array
     *  If time unit is day : $events[$day] = status
     *  else $events[$day][$minute] = status
     */
    public function asStatus($availability)
    {
        /** @var \MongoDate $dayMD */
        $dayMD = $availability['d'];
        $day = new \DateTime();
        $day->setTimestamp($dayMD->sec);
        $day = $day->format("Ymd");

        $timesRanges = $this->timeManager->getTimesRanges($availability, 1, false);
        $events = array();

        if (count($timesRanges)) {
            //Fill default listing status for all minutes of the day
            $events[$day] = array_fill_keys(array_keys(range(0, 1439)), $this->defaultListingStatus);

            //Replace minutes status by existing availability status
            foreach ($timesRanges as $i => $timeRange) {
                for ($m = $timeRange['start']; $m < $timeRange['end']; $m++) {
                    $events[$day][$m] = $timeRange['status'];
                }
            }
        } else {
            $events[$day] = $availability["s"];
        }

        return $events;
    }


    /**
     * Get ListingAvailability as calendar event
     * Id of event is equal to the concatenation of $this->getId() and start time
     *
     * @param ListingAvailability $availability
     *
     * @return array
     */
    public function asCalendarEvent($availability)
    {
        /** @var \MongoDate $dayMD */
        $dayMD = $availability['d'];
        $day = new \DateTime();
        $day->setTimestamp($dayMD->sec);
        $dayAsString = $dayEndAsString = $day->format("Y-m-d");//by default day is the same for an event

        $nextDay = clone $day;//if end time is 00:00 the day will be the next one
        $nextDay->add(new \DateInterval('P1D'));
        $nextDayAsString = $nextDay->format("Y-m-d");

        $timesRanges = $this->timeManager->getTimesRanges($availability);
        $events = array();

//        print_r($timesRanges);
        if (count($timesRanges)) {
            foreach ($timesRanges as $i => $timeRange) {
                $dayEndAsString = $dayAsString;
                if ($timeRange['end'] == '00:00') {
                    $dayEndAsString = $nextDayAsString;
                }

                $events[] = array(
                    'id' => $availability["_id"] . str_replace(":", "", $timeRange['start']),
                    'title' => $timeRange['price'] / 100,
                    /** @Ignore */
                    'className' => "cal-" . str_replace(
                            "entity.listing_availability.status.",
                            "",
                            ListingAvailabilityTime::$statusValues[$timeRange['status']]
                        ) . "-evt",
                    'start' => $dayAsString . " " . $timeRange['start'],
                    'end' => $dayEndAsString . " " . $timeRange['end'],
                    'editable' => true,
                    'allDay' => false
                );
            }
        } else {
            $allDay = false;
            if ($this->timeUnitIsDay) {
                $allDay = true;
            }
            $events[] = array(
                'id' => $availability["_id"] . "0000",
                'title' => $availability["p"] / 100,
                /** @Ignore */
                'className' => "cal-" . str_replace(
                        "entity.listing_availability.status.",
                        "",
                        ListingAvailability::$statusValues[$availability["s"]]
                    ) . "-evt",
                'start' => $dayAsString . " " . "00:00",
                'end' => $dayAsString . " " . "23:59",
                'editable' => true,
                'allDay' => $allDay,
            );
        }

        return $events;
    }


    /**
     * Construct time ranges from ListingAvailabilityTimes
     *
     * @param ListingAvailability $listingAvailability
     * @param int                 $addOneMinuteToEndTime 1 or 0
     * @param bool                $timeAsString
     *
     * @return array
     */
    public function getTimesRanges($listingAvailability, $addOneMinuteToEndTime = 1, $timeAsString = true)
    {
        $times = isset($listingAvailability["ts"]) ? $listingAvailability["ts"] : array();
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


    /**
     * @return bool
     */
    public function getTimeUnitIsDay()
    {
        return $this->timeUnitIsDay;
    }


    /**
     * Duplicate listing availabilities
     *
     * @param  int $listingId
     * @param  int $duplicatedListingId
     * @param  int $daysMaxEdition
     */
    public function duplicate($listingId, $duplicatedListingId, $daysMaxEdition)
    {
        $start = new \DateTime();
        $start->sub(new \DateInterval('P1D'));
        $end = new \DateTime();
        $end = $end->add(new \DateInterval('P' . $daysMaxEdition . 'D'));

        $availabilities = $this->getAvailabilitiesByListingAndDateRange($listingId, $start, $end);
        $newAvailabilities = array();
        foreach ($availabilities as $availability) {
            unset($availability["_id"]);
            $availability["lId"] = $duplicatedListingId;
            $newAvailabilities[] = $availability;
        }
        if (count($newAvailabilities)) {
            $this->collection->batchInsert($newAvailabilities);
        }
    }

    /**
     * Convert ListingAvailability object to array. Waiting 2.8 to use object normaliser
     *
     * @param ListingAvailability $availability
     * @return array
     */
    public function listingAvailabilityToArray(ListingAvailability $availability)
    {
        //convert object to array
        $availabilityAsArray = array(
            '_id' => new \MongoId($availability->getId()),
            'lId' => $availability->getListingId(),
            'd' => new \MongoDate($availability->getDay()->getTimestamp()),
            's' => $availability->getStatus(),
            'p' => intval($availability->getPrice()),
        );

        $times = array();
        foreach ($availability->getTimes() as $i => $time) {
            $times[] = array(
                '_id' => $time->getId(),
                's' => $time->getStatus(),
                'p' => intval($time->getPrice()),
            );
        }
        $availabilityAsArray["ts"] = $times;

        return $availabilityAsArray;
    }

    /**
     * @return bool
     */
    public function getTimeUnitIsDay()
    {
        return $this->timeUnitIsDay;
    }

    /**
     *
     * @return ListingAvailabilityTimeManager
     */
    public function getTimeManager()
    {
        return $this->timeManager;
    }

    /**
     *
     * @return ListingAvailabilityRepository
     */
    public function getRepository()
    {
        return $this->dm->getRepository('CocoricoCoreBundle:ListingAvailability');
    }


    /**
     * Save availability
     *
     * @param ListingAvailability $availability
     *
     */
    public function save(ListingAvailability $availability)
    {
        $this->dm->persist($availability);
        $this->dm->flush();
    }

}

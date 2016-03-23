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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Query;

class ListingAvailabilityManager
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
     * Save availabilities Status
     *
     * @param int          $listingId
     * @param DateRange    $dateRange
     * @param array        $weekDays
     * @param TimeRange [] $timeRanges
     * @param int|null     $status              ListingAvailability::$statusValues
     * @param int|null     $defaultPrice
     * @param bool         $endDayIncluded      For bookings it's equal to app endDayIncluded parameter else (status edition)
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
        $endDayIncluded,
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
            $endDayIncluded,
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
     * @param bool         $endDayIncluded      For bookings it's equal to app endDayIncluded parameter else (status edition)
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
        $endDayIncluded,
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
            $endDayIncluded,
            $bookingCancellation
        );
    }

    /**
     * Save availabilities Status Or Price depending on status or price value
     *
     * @param int          $listingId
     * @param DateRange    $dateRange
     * @param array        $weekDays
     * @param TimeRange [] $timeRanges
     * @param int|null     $status              ListingAvailability::$statusValues
     * @param int|null     $price
     * @param int|null     $defaultPrice
     * @param bool         $endDayIncluded      For bookings it's equal to app endDayIncluded parameter else (status edition)
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
        $endDayIncluded,
        $bookingCancellation
    ) {
        $typeModification = $status ? "status" : "price";

        $start = clone $dateRange->getStart();
        $end = clone $dateRange->getEnd();
        if ($endDayIncluded) {
            $end->modify('+1 day');
        }

        $interval = new \DateInterval('P1D');
        $days = new \DatePeriod($start, $interval, $end);

        /** @var \DateTime[] $days */
        foreach ($days as $i => $day) {
            //Is day in weekdays
            if (!count($weekDays) || in_array($day->format('N'), $weekDays)) {
                $dayP1 = new \DateTime($day->format("Y-m-d"));
                $dayP1->add(new \DateInterval('P1D'));

                //Get availability for this day if exist
                $existingAvailabilities = $this->getAvailabilitiesByListingAndDateRange(
                    $listingId,
                    $day,
                    $dayP1
                );

                /** @var ListingAvailability $availability */
                if ($existingAvailabilities->count()) {
                    $availability = $existingAvailabilities->getSingleResult();
                } else {
                    $availability = new ListingAvailability();
                    $availability->setListingId($listingId);
                    $availability->setDay($day);
                }

                //No modification of booked availability for time unit day mode except when a booking is cancelled
                if (!$bookingCancellation) {
                    if ($this->timeUnitIsDay && $availability->getStatus() == ListingAvailability::STATUS_BOOKED) {
                        continue;
                    }
                }

                if ($typeModification == "status") {
                    $availability = $this->setAvailabilityStatus($availability, $status, $defaultPrice);
                } elseif ($typeModification == "price") {
                    $availability = $this->setAvailabilityPrice($availability, $price);
                } else {
                    throw new \Exception('Status or Price is required');
                }

                $times = $this->mergeAvailabilityTimes(
                    $availability,
                    $timeRanges,
                    $typeModification,
                    $defaultPrice,
                    $bookingCancellation
                );

                //No time range means all day
                if (!count($timeRanges)) {
                    $availability->setTimes(new ArrayCollection(array()));
                } else {
                    $availability->setTimes(new ArrayCollection($times));
                }

                $this->dm->persist($availability);
                $this->dm->flush();
            }
        }


    }

    /**
     * Set availabilities Status
     *
     * @param ListingAvailability $availability
     * @param int                 $status
     * @param int                 $defaultPrice
     *
     * @return ListingAvailability
     */
    public function setAvailabilityStatus($availability, $status, $defaultPrice)
    {
        $availability->setStatus($status);
        if (!$availability->getId()) {
            $availability->setPrice($defaultPrice);
        }

        return $availability;
    }

    /**
     * Set availabilities Price
     *
     * @param ListingAvailability $availability
     * @param int                 $price
     *
     * @return ListingAvailability
     */
    public function setAvailabilityPrice($availability, $price)
    {
        $availability->setPrice($price);
        if (!$availability->getId()) {
            $availability->setStatus(ListingAvailability::STATUS_AVAILABLE);
        }

        return $availability;
    }


    /**
     * Merge existing and new times. The result will replace all existing embedded ListingAvailabilityTime embed documents
     * for this day and this listing.
     *
     * @param ListingAvailability    $availability
     * @param TimeRange []           $timeRanges
     * @param string  (price|status) $typeModification
     * @param int                    $defaultPrice
     * @param bool                   $bookingCancellation
     *
     * @return ListingAvailabilityTime[]
     */
    public function mergeAvailabilityTimes(
        ListingAvailability $availability,
        array $timeRanges,
        $typeModification,
        $defaultPrice,
        $bookingCancellation
    ) {
        /** @var ListingAvailabilityTime[] $times */
        $times = array();
        foreach ($availability->getTimes() as $l => $existingTime) {
            $times[intval($existingTime->getId())] = $existingTime;
        }

        //Get new times
        $status = $availability->getStatus();
        $price = $availability->getPrice();
        foreach ($timeRanges as $j => $timeRange) {
            /** @var \DateTime $startTime H:i */
            $startTime = $timeRange->getStart();
            $endTime = $timeRange->getEnd();

            //The start minute number
            $startMinute = intval($startTime->getTimestamp() / 60);
            $endMinute = intval($endTime->getTimestamp() / 60);
//            $nbMinutes = intval(($endTime->getTimestamp() - $startTime->getTimestamp()) / 60);
//            $nbMinutes += $startMinute;

            //Replace existing minutes with new ones and add new ones if they don't exist
            for ($k = $startMinute; $k < $endMinute; $k++) {
                if (isset($times[$k])) {
                    $time = $times[$k];
                } else {
                    $time = new ListingAvailabilityTime();
                }

                if ($typeModification == "status") {
                    $time = $this->setAvailabilityTimeStatus($time, $status, $defaultPrice, $bookingCancellation);
                } else {
                    $time = $this->setAvailabilityTimePrice($time, $price);
                }
                //For new time
                $time->setId($k);

                $times[$k] = $time;
            }
        }
//        die();
        ksort($times);

        return $times;
    }

    /**
     * Set availabilities Status
     *
     * @param ListingAvailabilityTime $availabilityTime
     * @param int                     $status
     * @param int                     $defaultPrice
     * @param bool                    $bookingCancellation
     *
     * @return ListingAvailabilityTime
     */
    public function setAvailabilityTimeStatus($availabilityTime, $status, $defaultPrice, $bookingCancellation)
    {
        //Status modification allowed only when listing availability time is not booked or when a booking is cancelled
        if ($availabilityTime->getStatus() != ListingAvailabilityTime::STATUS_BOOKED || $bookingCancellation) {
            $availabilityTime->setStatus($status);
        }

        if (!$availabilityTime->getId() && $availabilityTime->getId() !== 0) {
            $availabilityTime->setPrice($defaultPrice);
        }

        return $availabilityTime;
    }

    /**
     * Set availabilities Price
     *
     * @param ListingAvailabilityTime $availabilityTime
     * @param int                     $price
     *
     * @return ListingAvailabilityTime
     */
    public function setAvailabilityTimePrice($availabilityTime, $price)
    {
        //Price modification allowed only when listing is not booked
        if ($availabilityTime->getStatus() != ListingAvailabilityTime::STATUS_BOOKED) {
            $availabilityTime->setPrice($price);
        }

        if (!$availabilityTime->getId() && $availabilityTime->getId() !== 0) {
            $availabilityTime->setStatus(ListingAvailability::STATUS_AVAILABLE);
        }

        return $availabilityTime;
    }

    /**
     * Save availability times for one availability
     *
     * @param ListingAvailability    $availability
     * @param                        $startTime
     * @param                        $endTime
     * @param string  (price|status) $typeModification
     * @param int                    $defaultPrice
     */

    public function saveAvailabilityTimes(
        ListingAvailability $availability,
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

        $availability->setTimes(new ArrayCollection($times));

        $this->dm->persist($availability);
        $this->dm->flush();
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
        $availabilities = $this->getRepository()->getAvailabilitiesByListingAndDateRange(
            $listingId,
            $start,
            $end,
            $endDayIncluded
        );

        switch ($format) {
            case 'calendar' :
                foreach ($availabilities as $availability) {
                    $events = array_merge($events, $this->asCalendarEvent($availability));
                }
                break;
            default:
                $events = $availabilities;
                break;
        }


        return $events;
    }

    /**
     * Get ListingAvailability as calendar event
     * Id of event is equal to the concatenation of $this->getId() and start time
     *
     * @param ListingAvailability $listingAvailability
     *
     * @return array
     */
    public function asCalendarEvent(ListingAvailability $listingAvailability)
    {
        $day = $listingAvailability->getDay()->format("Y-m-d");
        $timesRanges = $this->getTimesRanges($listingAvailability);
        $events = array();

//        print_r($timesRanges);
        if (count($timesRanges)) {
            foreach ($timesRanges as $i => $timeRange) {
                $events[] = array(
                    'id' => $listingAvailability->getId() . str_replace(":", "", $timeRange['start']),
                    'title' => $timeRange['price'] / 100,
//                  'description' => "",
                    /** @Ignore */
                    'className' => "cal-" . str_replace(
                            "entity.listing_availability.status.",
                            "",
                            ListingAvailabilityTime::$statusValues[$timeRange['status']]
                        ) . "-evt",
                    'start' => $day . " " . $timeRange['start'],
                    'end' => $day . " " . $timeRange['end'],
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
                'id' => $listingAvailability->getId() . "0000",
                'title' => $listingAvailability->getPriceDecimal(),
//              'description' => "",
                /** @Ignore */
                'className' => "cal-" . str_replace(
                        "entity.listing_availability.status.",
                        "",
                        $listingAvailability->getStatusText()
                    ) . "-evt",
                'start' => $day . " " . "00:00",
                'end' => $day . " " . "23:59",
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
     *
     * @return array
     */
    public function getTimesRanges(ListingAvailability $listingAvailability, $addOneMinuteToEndTime = 1)
    {
        $times = $listingAvailability->getTimes();
        $timesRanges = $range = array();
        $prevStatus = $prevId = $prevPrice = false;

        foreach ($times as $i => $time) {
            if ($time->getStatus() !== $prevStatus || $time->getId() != ($prevId + 1) || $time->getPrice(
                ) !== $prevPrice
            ) {
                if ($prevStatus !== false && $prevId !== false) {
                    $range['end'] = date('H:i', mktime(0, $prevId + $addOneMinuteToEndTime));
                    $timesRanges[] = $range;
                    //$range = array();
                }

                $range = array(
                    'start' => date('H:i', mktime(0, $time->getId())),
                    'status' => $time->getStatus(),
                    'price' => $time->getPrice()
                );
            }

            $prevStatus = $time->getStatus();
            $prevPrice = $time->getPrice();
            $prevId = $time->getId();
        }

        if ($times->count()) {
            $range['end'] = date('H:i', mktime(0, $times->last()->getId() + $addOneMinuteToEndTime));
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
        foreach ($availabilities as $availability) {
            $availabilityCloned = clone $availability;
            $availabilityCloned->setListingId($duplicatedListingId);
            $this->dm->persist($availabilityCloned);
            $this->dm->flush();
        }
    }

    /**
     *
     * @return ListingAvailabilityRepository
     */
    public function getRepository()
    {
        return $this->dm->getRepository('CocoricoCoreBundle:ListingAvailability');
    }

}

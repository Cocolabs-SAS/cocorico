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
use Cocorico\CoreBundle\Repository\ListingAvailabilityRepository;
use Cocorico\TimeBundle\Model\DateTimeRange;
use Cocorico\TimeBundle\Model\TimeRange;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use MongoDate;
use MongoId;

class ListingAvailabilityManager
{
    protected $dm;
    protected $timeManager;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $defaultListingStatus;
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
        $this->endDayIncluded = $endDayIncluded;
    }

    /**
     * Save availabilities Status
     *
     * @param int           $listingId
     * @param DateTimeRange $dateTimeRange
     * @param array         $weekDays
     * @param int|null      $status              ListingAvailability::$statusValues
     * @param int|null      $defaultPrice
     *                                           param bool         $endDayIncluded      For bookings it's equal to app endDayIncluded parameter else (status edition)
     *                                           it's false
     * @param bool          $bookingCancellation Booked status can be only modified when booking is canceled
     * @param string        $timezone
     *
     * @throws Exception
     */
    public function saveAvailabilitiesStatus(
        $listingId,
        DateTimeRange $dateTimeRange,
        array $weekDays,
        $status,
        $defaultPrice,
        $bookingCancellation,
        $timezone = 'UTC'
    ) {
        $this->saveAvailabilities(
            $listingId,
            $dateTimeRange,
            $weekDays,
            $status,
            null,
            $defaultPrice,
            $bookingCancellation,
            $timezone
        );
    }

    /**
     * Save availabilities Prices
     *
     * @param int           $listingId
     * @param DateTimeRange $dateTimeRange       ,
     * @param array         $weekDays
     * @param int           $price
     * @param bool          $bookingCancellation Booked status can be only modified when booking is canceled
     * @param string        $timezone
     *
     * @throws Exception
     */
    public function saveAvailabilitiesPrices(
        $listingId,
        DateTimeRange $dateTimeRange,
        array $weekDays,
        $price,
        $bookingCancellation,
        $timezone = 'UTC'
    ) {
        $this->saveAvailabilities(
            $listingId,
            $dateTimeRange,
            $weekDays,
            null,
            $price,
            null,
            $bookingCancellation,
            $timezone
        );
    }

    /**
     * Save availabilities status or price depending on status or price value
     *
     * @param int           $listingId
     * @param DateTimeRange $dateTimeRange
     * @param array         $weekDays
     * @param int|null      $status              ListingAvailability::$statusValues
     * @param int|null      $price
     * @param int|null      $defaultPrice
     * @param bool          $bookingCancellation Booked status can be only modified when booking is canceled
     * @param string        $timezone
     *
     * @throws Exception
     */
    public function saveAvailabilities(
        $listingId,
        DateTimeRange $dateTimeRange,
        array $weekDays,
        $status,
        $price,
        $defaultPrice = null,
        $bookingCancellation,
        $timezone = 'UTC'
    ) {

        if ($this->timeUnitIsDay) {
            $dateTimeRange->setTimeRanges(array());
        }
        $daysTimeRanges = $dateTimeRange->getDaysTimeRanges($this->endDayIncluded, $weekDays, $timezone);

//        print_r($daysTimeRanges);
//        die();

        foreach ($daysTimeRanges as $dayTimeRanges) {
            $this->saveAvailability(
                $listingId,
                $dayTimeRanges->day,
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
     * @param int      $listingId
     * @param DateTime $day
     * @param array    $timeRanges
     * @param int      $status
     * @param int      $price
     * @param int|null $defaultPrice
     * @param bool     $bookingCancellation
     *
     * @throws MongoDBException
     */
    private function saveAvailability(
        $listingId,
        DateTime $day,
        array $timeRanges,
        $status,
        $price,
        $defaultPrice = null,
        $bookingCancellation
    ) {
        $collection = $this->dm->getDocumentCollection('CocoricoCoreBundle:ListingAvailability');
        $typeModification = $status ? 'status' : 'price';

        //Is day in weekdays
        $availability = $this->getAvailability($listingId, $day);

        //No modification of booked availability for time unit day mode except when a booking is cancelled
        if (!$bookingCancellation) {
            if ($this->timeUnitIsDay && $availability["s"] == ListingAvailability::STATUS_BOOKED) {
                return;
            }
        }

        if ($typeModification == 'status') {
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
            $availability['ts'] = array();
        } else {
            $availability['ts'] = $times;
        }

        $collection->save($availability);
    }

    /**
     * Get availability by listingId and day
     *
     * @param int      $listingId
     * @param DateTime $day
     *
     * @return array
     */
    private function getAvailability($listingId, $day)
    {
        $dayP1 = new DateTime($day->format('Y-m-d'));
        $dayP1->add(new DateInterval('P1D'));

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
            $availability['lId'] = $listingId;
            $availability['d'] = new MongoDate($day->getTimestamp());
            $availability['s'] = null;
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
        $availability['s'] = $status;
        if (!isset($availability['_id']) || !$availability['_id']) {
            $availability['p'] = intval($defaultPrice);
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
        $availability['p'] = intval($price);
        if (!isset($availability['_id']) || !$availability['_id']) {
            $availability['s'] = ListingAvailability::STATUS_AVAILABLE;
        }

        return $availability;
    }


    /**
     * Get ListingAvailability of a listing by date range
     *
     * @param int      $listingId
     * @param DateTime $start
     * @param DateTime $end
     * @param boolean  $endDayIncluded
     *
     * @return array|Cursor|ListingAvailability[]
     */
    public function getAvailabilitiesByListingAndDateRange(
        $listingId,
        DateTime $start,
        DateTime $end,
        $endDayIncluded = false
    ) {
        return $this->getRepository()->findAvailabilitiesByListing(
            $listingId,
            $start,
            $end,
            $endDayIncluded,
            false
        );
    }


    /**
     * @param int      $listingId
     * @param DateTime $start
     * @param DateTime $end
     * @param bool     $endDayIncluded
     * @param string   $timezone
     * @return array
     */
    public function getCalendarEvents(
        $listingId,
        DateTime $start,
        DateTime $end,
        $endDayIncluded = false,
        $timezone
    ) {
        $availabilities = $this->getAvailabilitiesByListingAndDateRange(
            $listingId,
            $start,
            $end,
            $endDayIncluded
        );

        return $this->asCalendarEvents($availabilities, $timezone);
    }


    /**
     * @param int      $listingId
     * @param DateTime $start
     * @param DateTime $end
     * @param bool     $endDayIncluded
     * @return array
     */
    public function getAvailabilitiesStatus(
        $listingId,
        DateTime $start,
        DateTime $end,
        $endDayIncluded = false
    ) {
        $status = array();
        $availabilities = $this->getAvailabilitiesByListingAndDateRange(
            $listingId,
            $start,
            $end,
            $endDayIncluded
        );

        foreach ($availabilities as $availability) {
            $status = $status + $this->asStatus($availability);
        }

        return $status;
    }

    /**
     * Get ListingAvailability as status raw
     *
     * @param array $availability
     *
     * @return array
     *  If time unit is day : $events[$day] = status
     *  else $events[$day][$minute] = status
     */
    public function asStatus($availability)
    {
        /** @var MongoDate $dayMD */
        $dayMD = $availability['d'];
        $day = new DateTime();
        $day->setTimestamp($dayMD->sec);
        $day = $day->format('Ymd');

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
            $events[$day] = $availability['s'];
        }

        return $events;
    }

    /**
     * Return all availabilities data organized by time range
     *
     * @param array  $availabilities
     * @param string $timezone
     * @return array
     */
    public function asCalendarEvents($availabilities, $timezone)
    {
        $daysEvents = array();
        foreach ($availabilities as $availability) {
            $events = $this->asCalendarDayEvents($availability, $timezone);
//            print_r($events);
            $daysEvents = array_merge_recursive($daysEvents, $events);
        }

        $allDay = $this->timeUnitIsDay ? true : false;
        $result = array();
        foreach ($daysEvents as $dayEvents) {
            $prevEvent = null;
            foreach ($dayEvents as $i => $event) {
                $status = ListingAvailability::$statusValues[$event['status']];
                /** @Ignore */
                $status = str_replace('entity.listing_availability.status.', '', $status);

                //If current start event is equal to previous end and event time and price are equals then events are merged
                if ($prevEvent && $event['start'] == $prevEvent['end']) {
                    if ($prevEvent['price'] == $event['price'] && $prevEvent['status'] == $event['status']) {
                        array_splice($result, count($result) - 1, 1);
                        $event['start'] = $prevEvent['start'];
                    }
                }
                $prevEvent = $event;

                //Get start and end in UTC
                $startUTC = new DateTime($event['start'], new DateTimeZone($timezone));
                $startUTC->setTimezone(new DateTimeZone('UTC'));
                $endUTC = new DateTime($event['end'], new DateTimeZone($timezone));
                $endUTC->setTimezone(new DateTimeZone('UTC'));

                $result[] = array(
                    'id' => $event['id'],
                    'title' => $event['price'] / 100,
                    'className' => 'cal-' . $status . '-evt',
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'startUTC' => $startUTC->format('Y-m-d H:i'),
                    'endUTC' => $endUTC->format('Y-m-d H:i'),
                    'editable' => true,
                    'allDay' => $allDay
                );
            }
        }

//        print_r($result);
        return $result;
    }

    /**
     * Return availability data organized by time range for availability day
     *
     * @param array  $availability
     * @param string $timezone
     * @return array
     */
    private function asCalendarDayEvents($availability, $timezone)
    {
        /** @var MongoDate $dayMD */
        $dayMD = $availability['d'];
        $day = new DateTime();
        $day->setTimestamp($dayMD->sec);
        $dayAsString = $dayEndAsString = $day->format('Y-m-d');//by default day is the same for an event

        $nextDay = clone $day;//if end time is 00:00 the day will be the next one
        $nextDay->add(new DateInterval('P1D'));
        $nextDayAsString = $nextDay->format('Y-m-d');

        //time ranges array in UTC
        $timesRanges = $this->timeManager->getTimesRanges($availability);
//        print_r($timesRanges);

        $events = array();
        if (count($timesRanges)) {
            foreach ($timesRanges as $timeRange) {
                $dayEndAsString = $dayAsString;
                if ($timeRange['end'] == '00:00') {
                    $dayEndAsString = $nextDayAsString;
                }

                $startUTC = new DateTime($dayAsString.' '.$timeRange['start']);
                $endUTC = new DateTime($dayEndAsString.' '.$timeRange['end']);

//                echo $startUTC->format('Y-m-d H:i') . ' / ' . $endUTC->format('Y-m-d H:i') . '<br>';

                $start = clone $startUTC;
                $end = clone $endUTC;
                $start->setTimezone(new DateTimeZone($timezone));
                $end->setTimezone(new DateTimeZone($timezone));

//                echo $start->format('Y-m-d H:i') . ' / ' . $end->format('Y-m-d H:i') . '<br>';

                //If time range span days then it is splitted from start to midnight and midnight to end
                /** @var TimeRange[] $subTimeRanges */
                $subTimeRanges = array(new TimeRange($start, $end, $start));
                if ($start->format('Y-m-d') != $end->format('Y-m-d')) {
                    $midnight = clone $end;
                    $midnight->setTime(0, 0, 0);

                    $subTimeRanges = array(
                        new TimeRange($start, $midnight, $start),
                        new TimeRange($midnight, $end, $midnight),
                    );
                }

                foreach ($subTimeRanges as $index => $subTimeRange) {
                    $subStart = $subTimeRange->getStart();
                    $subEnd = $subTimeRange->getEnd();
                    if ($subStart->format('Y-m-d H:i') != $subEnd->format('Y-m-d H:i')) {

                        $events[$subStart->format('Y-m-d')][] = array(
                            'id' => $availability['_id'] . str_replace(':', '', $subStart->format('H:i')),
                            'start' => $subStart->format('Y-m-d H:i'),
                            'end' => $subEnd->format('Y-m-d H:i'),
                            'status' => $timeRange['status'],
                            'price' => $timeRange['price'],
                        );
                    }
                }
            }
        } else {
            $start = new DateTime($dayAsString.' '.'00:00');
            $end = new DateTime($dayAsString.' '.'23:59');

            $events[$start->format('Y-m-d')][] = array(
                'id' => $availability['_id'] . '0000',
                'start' => $start->format('Y-m-d H:i'),
                'end' => $end->format('Y-m-d H:i'),
                'status' => $availability['s'],
                'price' => $availability['p'],
            );
        }

        return $events;
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
        $start = new DateTime();
        $start->sub(new DateInterval('P1D'));
        $end = new DateTime();
        $end = $end->add(new DateInterval('P'.$daysMaxEdition.'D'));

        $availabilities = $this->getAvailabilitiesByListingAndDateRange($listingId, $start, $end);
        $newAvailabilities = array();
        foreach ($availabilities as $availability) {
            unset($availability["_id"]);
            $availability["lId"] = $duplicatedListingId;
            $newAvailabilities[] = $availability;
        }
        if (count($newAvailabilities)) {
            $this->dm->getDocumentCollection('CocoricoCoreBundle:ListingAvailability')->batchInsert($newAvailabilities);
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
            '_id' => new MongoId($availability->getId()),
            'lId' => $availability->getListingId(),
            'd' => new MongoDate($availability->getDay()->getTimestamp()),
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
        $availabilityAsArray['ts'] = $times;

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

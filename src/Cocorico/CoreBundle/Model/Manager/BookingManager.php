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
use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Event\BookingAmountEvent;
use Cocorico\CoreBundle\Event\BookingAmountEvents;
use Cocorico\CoreBundle\Event\BookingEvent;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\CoreBundle\Event\BookingPayinRefundEvent;
use Cocorico\CoreBundle\Event\BookingValidateEvent;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\CoreBundle\Repository\BookingRepository;
use Cocorico\CoreBundle\Repository\ListingDiscountRepository;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BookingManager extends BaseManager
{
    protected $em;
    protected $dm;
    protected $listingAvailabilityManager;
    protected $mailer;
    protected $smser;
    protected $dispatcher;
    protected $feeAsAsker;
    protected $feeAsOfferer;
    protected $endDayIncluded;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $timeZone;
    protected $timesMax;
    protected $hoursAvailable;
    protected $expirationDelay;
    protected $acceptationDelay;
    protected $minStartDelay;
    protected $minStartTimeDelay;
    protected $allowSingleDay;
    protected $defaultListingStatus;
    protected $vatRate;
    protected $includeVat;
    protected $bundles;
    public $minPrice;
    public $maxPerPage;

    /**
     * @param EntityManager                           $em
     * @param DocumentManager                         $dm
     * @param ListingAvailabilityManager              $listingAvailabilityManager
     * @param TwigSwiftMailer                         $mailer
     * @param \Cocorico\SMSBundle\Twig\TwigSmser|null $smser
     * @param EventDispatcherInterface                $dispatcher
     * @param array                                   $parameters
     *        float     $feeAsAsker
     *        float     $feeAsOfferer
     *        boolean   $endDayIncluded
     *        int       $timeUnit App time unit includeVat
     *        int       $timesMax Max times unit if time_unit includeVat
     *        array     $hoursAvailable
     *        int       $minStartDelay
     *        int       $minStartTimeDelay
     *        int       $minPrice
     *        int       $maxPerPage
     *        int       $defaultListingStatus
     *        float     $vatRate
     *        bool      $includeVat
     */
    public function __construct(
        EntityManager $em,
        DocumentManager $dm,
        ListingAvailabilityManager $listingAvailabilityManager,
        TwigSwiftMailer $mailer,
        $smser,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->em = $em;
        $this->dm = $dm;
        $this->listingAvailabilityManager = $listingAvailabilityManager;
        $this->mailer = $mailer;
        $this->smser = $smser;
        $this->dispatcher = $dispatcher;

        //Parameters
        $parameters = $parameters["parameters"];
        $this->feeAsAsker = $parameters["cocorico_fee_as_asker"];
        $this->feeAsOfferer = $parameters["cocorico_fee_as_offerer"];
        $this->timeUnit = $parameters["cocorico_time_unit"];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;
        $this->timeZone = $parameters["cocorico_time_zone"];
        $this->timesMax = $parameters["cocorico_time_max"];
        $this->hoursAvailable = $parameters["cocorico_time_hours_available"];

        $this->allowSingleDay = $parameters["cocorico_booking_allow_single_day"];
        $this->endDayIncluded = $parameters["cocorico_booking_end_day_included"];
        if ($this->allowSingleDay) {
            $this->endDayIncluded = true;
        }
        $this->expirationDelay = $parameters["cocorico_booking_expiration_delay"];
        $this->acceptationDelay = $parameters["cocorico_booking_acceptation_delay"];
        $this->minStartDelay = $parameters["cocorico_booking_min_start_delay"];
        $this->minStartTimeDelay = $parameters["cocorico_booking_min_start_time_delay"];
        $this->minPrice = $parameters["cocorico_booking_price_min"];

        $this->maxPerPage = $parameters["cocorico_dashboard_max_per_page"];
        $this->defaultListingStatus = $parameters["cocorico_listing_availability_status"];
        $this->vatRate = $parameters["cocorico_vat"];
        $this->includeVat = $parameters["cocorico_include_vat"];
        $this->bundles = $parameters["cocorico_bundles"];
    }


    /**
     * Pre-set new Booking based data.
     *
     * @param Listing           $listing
     * @param User|null         $user
     * @param DateRange         $dateRange
     * @param TimeRange|boolean $timeRange
     * @return Booking
     */
    public function initBooking(Listing $listing, $user, DateRange $dateRange = null, $timeRange = null)
    {
        $booking = new Booking();
        $booking->setListing($listing);
        $booking->setUser($user);
        $booking->setStatus(Booking::STATUS_DRAFT);

        if ($dateRange && $dateRange->getStart() && $dateRange->getEnd()) {
            $dateRange->getStart()->setTime(0, 0, 0);
            $dateRange->getEnd()->setTime(0, 0, 0);
            $booking->setStart($dateRange->getStart());
            $booking->setEnd($dateRange->getEnd());

            if (!$this->timeUnitIsDay) {
                if ($timeRange && $timeRange->getStart() && $timeRange->getEnd()) {
                    $booking->setStartTime($timeRange->getStart());
                    $booking->setEndTime($timeRange->getEnd());
                }
            } else {
                $booking->setStartTime(new \DateTime('1970-01-01 00:00'));
                $booking->setEndTime(new \DateTime('1970-01-01 00:00'));
            }
        } else {
            if ($this->timeUnitIsDay) {
                $booking = $this->initBookingDatesInDayMode($booking);
            } else {
                $booking = $this->initBookingDatesInNotDayMode($booking);
            }
        }

        $booking->setCancellationPolicy($listing->getCancellationPolicy());

        return $booking;
    }

    /**
     * Init Booking day and hours with the first listing availability in the 30 next days
     *
     * @param Booking $booking
     * @return Booking
     */
    public function initBookingDatesInNotDayMode(Booking $booking)
    {
        //Special case not managed if allowSingleDay = false and endDayIncluded = true
        if ($this->timeUnitIsDay || ($this->allowSingleDay == false && $this->endDayIncluded == true)) {
            return $booking;
        }

        //Days:
        // Min start date is equal to today plus "minStartDelay" days plus "minStartTimeDelay" hours
        $minStartDate = new \DateTime();
        $minStartDate->setTimezone(new \DateTimeZone($this->getTimeZone()));
        $intervalFormat = 'P' . $this->minStartDelay . 'DT' . ($this->minStartTimeDelay + 60) . 'M';//Wa add 1 hour to not manage minutes available
        $minStartDate->add(new \DateInterval($intervalFormat));
        //Min start day without time
        $minStartDay = clone $minStartDate;
        $minStartDay->setTime(0, 0, 0);

        $maxEndDate = new \DateTime();
        $maxEndDate->add(new \DateInterval('P1M'));

        //Hours:
        //Min max hours depending on hours available parameters
        $minHour = $this->hoursAvailable[0] * 60;//in minutes
        $maxHour = $this->hoursAvailable[count($this->hoursAvailable) - 1] * 60;//in minutes

        //Get all listing unavailabilities from "minStartDay" until "maxEndDate"
        //Each minutes availabilities of each days are returned
        $availabilities = $this->listingAvailabilityManager->getAvailabilitiesByListingAndDateRange(
            $booking->getListing()->getId(),
            $minStartDay,
            $maxEndDate,
            'status',
            $this->endDayIncluded
        );

//        echo "minStartDate: " . $minStartDate->format('Y-m-d H:i') . "<br>" . "minHour: " . $minHour . "<br>" . "maxHour: " . $maxHour . "<br>";
//        print_r($availabilities);

        //Default day and hours values
        $dayToFind = $minStartDay;
        $hourToFind = intval($minStartDate->format('H')) * 60;//in minutes
        if ($hourToFind < $minHour) {
            $hourToFind = $minHour;
        } elseif ($hourToFind > $maxHour) {
            $hourToFind = $maxHour;
        }

        //We look for each days if some availabilities are defined
        $found = false;
        for ($d = clone $minStartDay; $d <= $maxEndDate; $d->add(new \DateInterval('P1D'))) {
            $dayToFind = $d;
            $availability = isset($availabilities[$d->format('Ymd')]) ? $availabilities[$d->format('Ymd')] : false;

            if (!$availability) {//No availability defined for this day
                if ($this->defaultListingStatus == ListingAvailability::STATUS_AVAILABLE) {//The listing is available by default
                    if ($d != $minStartDay) {//If d is not "minStartDay" the hour to find is the min hour available else the hour is the default one
                        $hourToFind = $minHour;
                    }
                    $found = true;
                    break;
                }
            } else {//An availability is defined for this day so we have to check if there are "timeUnit" consecutive minutes available
//                echo  "day:" . $d->format('Y-m-d'). "<br>";
                $nbMinutesAvailable = 0;
                //If current d is equal to "minStartDay" we start at the "minStartDate" hour
                $minHourBis = ($d == $minStartDay ? intval($minStartDate->format('H')) * 60 : $minHour);
                for ($m = $minHourBis; $m <= $maxHour; $m++) {
//                    echo $m . ":" . $availability[$m] . "<br>";
                    if ($nbMinutesAvailable == $this->timeUnit) {//Previous "timeUnit" minutes was available
                        $hourToFind = $m - $this->timeUnit;//This is the minute in the day of the first hour available
//                        echo "hour found:" . $hourToFind . "<br>";
                        if ($hourToFind >= $minHour && $hourToFind <= $maxHour) {//If the found hour is in the hours available range
//                            echo "found" . "<br>";
                            $found = true;
                            break;
                        } else {
                            $nbMinutesAvailable = 0;
                        }
                    } else {
                        $nbMinutesAvailable++;
                    }

                    if ($availability[$m] != ListingAvailability::STATUS_AVAILABLE) {
                        $nbMinutesAvailable = 0;
                    }
                }

                if ($found) {
                    break;
                }
            }
        }

        if ($found) {
            //Set Booking Day
            $dayToFind = new \DateTime($dayToFind->format('Y-m-d'));//Timezone is now default one (UTC)
            $booking->setStart($dayToFind);
            $booking->setEnd($dayToFind);
            if ($this->allowSingleDay == false && $this->endDayIncluded == false) {
                $endDayToFind = clone $dayToFind;
                $endDayToFind->add(new \DateInterval('P1D'));
                $booking->setEnd($endDayToFind);
            }

            //Set Booking Hours
            $startHour = new \DateTime();
            $startHour->setTimestamp($hourToFind * 60);//in seconds
            $endHour = clone $startHour;
            $endHour->add(new \DateInterval('PT' . ($this->timeUnit / 60) . 'H'));

            $booking->setStartTime($startHour);
            $booking->setEndTime($endHour);
        }

//        echo $booking->getStart()->format('Y-m-d') . "<br>" . $booking->getStartTime()->format('Y-m-d H:i') . "<br>" .$booking->getEnd()->format('Y-m-d') . "<br>" . $booking->getEndTime()->format('Y-m-d H:i') . "<br>";

        return $booking;
    }


    /**
     * Init Booking date with the first listing availability for time unit in day mode in the 30 next days
     *
     * @param Booking $booking
     * @return Booking
     */
    public function initBookingDatesInDayMode(Booking $booking)
    {
        //Special case not managed if allowSingleDay = false and endDayIncluded = true
        if (!$this->timeUnitIsDay || ($this->allowSingleDay == false && $this->endDayIncluded == true)) {
            return $booking;
        }

        //Days
        $minStartDay = new \DateTime();
        $minStartDay->setTimezone(new \DateTimeZone($this->getTimeZone()));
        $minStartDay->setTime(0, 0, 0);
        $intervalFormat = 'P' . $this->minStartDelay . 'D';
        $minStartDay->add(new \DateInterval($intervalFormat));

        $maxEndDay = new \DateTime();
        $maxEndDay->add(new \DateInterval('P1M'));

        //Get all listing unavailabilities from "minStartDay" until "maxEndDate"
        //Each availabilities of each days are returned
        $availabilities = $this->listingAvailabilityManager->getAvailabilitiesByListingAndDateRange(
            $booking->getListing()->getId(),
            $minStartDay,
            $maxEndDay,
            'status',
            $this->endDayIncluded
        );

//        echo "minStartDate: " . $minStartDay->format('Y-m-d H:i') . "<br>";

        //Default day value
        $dayToFind = $minStartDay;
        $found = false;
        for ($d = clone $minStartDay; $d <= $maxEndDay; $d->add(new \DateInterval('P1D'))) {
            $dayToFind = $d;
            $availability = isset($availabilities[$d->format('Ymd')]) ? $availabilities[$d->format('Ymd')] : false;

            if (!$availability) {//No availability defined for this day
                if ($this->defaultListingStatus == ListingAvailability::STATUS_AVAILABLE) {//The listing is available by default
                    $found = true;
                    break;
                }
            } else {//An availability is defined for this day so we have to check its availability
                if ($availability == ListingAvailability::STATUS_AVAILABLE) {//The listing is available
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            //Set Booking Day
            $dayToFind = new \DateTime($dayToFind->format('Y-m-d'));//Timezone is now default one (UTC)
            $booking->setStart($dayToFind);
            $booking->setEnd($dayToFind);
            if ($this->allowSingleDay == false && $this->endDayIncluded == false) {
                $endDayToFind = clone $dayToFind;
                $endDayToFind->add(new \DateInterval('P1D'));
                $booking->setEnd($endDayToFind);
            }

            //Set Booking Hour
            $booking->setStartTime(new \DateTime('1970-01-01 00:00'));
            $booking->setEndTime(new \DateTime('1970-01-01 00:00'));
        }

        return $booking;
    }


    /**
     * Check Booking Availability And Set Booking Amounts if no error.
     * Return the following errors if any :
     *  - self_booking_invalid => listing can not be booked by listing owner
     *  - unavailable => listing is unavailable
     *  - duration_invalid => booking duration is invalid
     *  - see checkBookingDates returns
     *
     * @param Booking $booking
     *
     * @return array errors
     * @throws \Exception
     */
    public function checkBookingAvailabilityAndSetAmounts(Booking &$booking)
    {
        $amount = 0;
        $errors = $this->checkBookingDates($booking);

        if (count($errors)) {
            return $errors;
        }
        $listing = $booking->getListing();
        $listingAvailabilityRepository = $this->dm->getRepository('CocoricoCoreBundle:ListingAvailability');
        $listingAvailabilities = $listingAvailabilityRepository->getAvailabilitiesByListingAndDateRange(
            $listing->getId(),
            $booking->getStart(),
            $booking->getEnd(),
            $this->endDayIncluded,
            false
        );
        //echo "nb listingAvailabilities" . $listingAvailabilities->count() . "<br>";

        $bookingDuration = $booking->getDuration($this->endDayIncluded, $this->timeUnit);

        //echo "bookingDuration" . $bookingDuration . "<br>";
        //Invalid duration
        if ($bookingDuration === false
            || ($listing->getMinDuration() && $bookingDuration < $listing->getMinDuration())
            || ($listing->getMaxDuration() && $bookingDuration > $listing->getMaxDuration())
            || $bookingDuration <= 0
        ) {
            $errors[] = 'duration_invalid';
        } //Unavailable
        elseif (
            $this->defaultListingStatus == ListingAvailability::STATUS_UNAVAILABLE
            && !$listingAvailabilities->count()
        ) {
            $errors[] = 'unavailable';
        } else {
            $price = $listing->getPrice();
            $amount = $bookingDuration * $price;
            $amountByMinute = $price / $this->timeUnit;

            $startMinute = intval($booking->getStartTime()->getTimestamp() / 60);
            $endMinute = intval($booking->getEndTime()->getTimestamp() / 60);
            if ($booking->getEndTime()->format('H:i') == '00:00') {
                $endMinute = 1440;
            }
            foreach ($listingAvailabilities as $listingAvailability) {
                //Listing availability
                if ($this->getTimeUnitIsDay()) {
                    //Availability
                    if (count($errors) ||
                        $listingAvailability["s"] == ListingAvailability::STATUS_UNAVAILABLE ||
                        $listingAvailability["s"] == ListingAvailability::STATUS_BOOKED
                    ) {
                        $amount = 0;
                        $errors[] = 'unavailable';
                        break;
                    }
                    $amount -= $price;
                    $amount += $listingAvailability["p"];
                } else {
                    if (count($errors)) {
                        break;
                    }

                    if (!isset($listingAvailability["ts"])) {
                        throw new \Exception(
                            "Time unit application parameter seems to have been changed from day to hour(s)."
                        );
                    }
                    //Compute amount from existing times
                    $existingTimes = $listingAvailability["ts"];
                    //echo "existingTimes" . print_r($existingTimes, 1) . "<br>";

                    $times = array();
                    foreach ($existingTimes as $l => $existingTime) {
                        $times[intval($existingTime["_id"])] = $existingTime;
                    }

                    //Price defined for each minute are defined for one time unit (hour, ...)
                    for ($k = $startMinute; $k < $endMinute; $k++) {
                        //If price is defined for this minute,
                        if (isset($times[$k])) {
                            if ($times[$k]["s"] == ListingAvailability::STATUS_UNAVAILABLE ||
                                $times[$k]["s"] == ListingAvailability::STATUS_BOOKED
                            ) {
                                $amount = 0;
                                $errors[] = 'unavailable';
                                break;
                            }
                            //We add to amount the corresponding price reported to one minute
                            $amount -= $amountByMinute;
                            $amount += $times[$k]["p"] / $this->timeUnit;
                        } else {
                            //We add to amount the price for the corresponding day reported to one minute
                            $amount -= $amountByMinute;
                            $amount += $listing->getPrice() / $this->timeUnit;
                        }
                    }

                }
            }
        }

        if (!count($errors)) {
            $booking->setAmount($amount);

            //Discount
            /** @var ListingDiscountRepository $listingDiscountRepository */
            $listingDiscountRepository = $this->em->getRepository("CocoricoCoreBundle:ListingDiscount");
            $discount = $listingDiscountRepository->findOneByFromQuantity($listing->getId(), $bookingDuration);
            if ($discount) {
                $booking->setAmount($booking->getAmount() - ($discount->getDiscount() / 100) * $booking->getAmount());
            }

            //Booking amount modifications before booking amount and fees setting
            try {
                $event = new BookingAmountEvent($booking, $discount);
                $this->dispatcher->dispatch(BookingAmountEvents::BOOKING_PRE_AMOUNTS_SETTING, $event);
                $booking = $event->getBooking();
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }

            if (!in_array('amount_invalid', $errors)) {
                if ($booking->getAmount() > 0 && $booking->getAmount() >= $this->minPrice) {
                    //Booking amount and fees are setted here
                    $booking = $this->setBookingAmounts($booking);

                    //Booking amount modifications after booking amount and fees setting
                    try {
                        $event = new BookingAmountEvent($booking, $discount);
                        $this->dispatcher->dispatch(BookingAmountEvents::BOOKING_POST_AMOUNTS_SETTING, $event);
                        $booking = $event->getBooking();
                    } catch (\Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                } else {
                    $errors[] = 'amount_invalid';
                }
            }
        }

        return $errors;
    }

    /**
     * //todo: Check factorization with DateRangeValidator->onPostBind
     * Check if booking dates are correct:
     *  Return :
     *      - date_range.invalid.min_start if start date is incorrect (start is greater than today plus minStartDelay days)
     *      - date_range.invalid.max_end if end date is incorrect (More than one year later)
     *      - date_range.invalid.end_before_start if start is after end
     *      - time_range.invalid.end_before_start if start time is after end
     *      - time_range.invalid.single_time if start time = end time
     *      - time_range.invalid.duration if duration > to max times unit (maxTimes)
     *      - time_range.invalid.min_start if start time is incorrect (start time is greater than today plus minStartTimeDelay hours)
     *
     * Errors are translated in DateRangeValidator and TimeRangeValidator
     *
     * @param Booking $booking
     *
     * @return array errors
     */
    protected function checkBookingDates(Booking $booking)
    {
        $errors = array();

        $minStart = new \DateTime();
        $minStart->setTimezone(new \DateTimeZone($this->getTimeZone()));
        if ($this->minStartDelay > 0) {
            $minStart->add(new \DateInterval('P' . $this->minStartDelay . 'D'));
        }

        if ($booking->getStart()) {
            $interval = $minStart->diff($booking->getStart())->format('%r%a');
            if ($interval < 0) {
                $errors[] = 'date_range.invalid.min_start';
            }

            $oneYearLater = $minStart->add(new \DateInterval('P1Y'));
            if ($booking->getEnd() > $oneYearLater) {
                $errors[] = 'date_range.invalid.max_end';
            }

            if ($booking->getStart() > $booking->getEnd()) {
                $errors[] = 'date_range.invalid.end_before_start';
            }

            if (!$this->timeUnitIsDay) {
                if (!$booking->getStartTime() || !$booking->getEndTime()) {
                    $errors[] = 'time_range.invalid.required';
                }
                if ($booking->getStartTime() > $booking->getEndTime() &&
                    $booking->getEndTime()->format('H:i') != '00:00'
                ) {
                    $errors[] = 'time_range.invalid.end_before_start';
                } elseif ($booking->getStartTime() == $booking->getEndTime()) {
                    $errors[] = 'time_range.invalid.single_time';
                }

                if ($booking->getStartTime() && $booking->getEndTime()) {
                    $timeRange = new TimeRange($booking->getStartTime(), $booking->getEndTime());
                    $duration = $timeRange->getDuration($this->timeUnit);
                    if ($duration > $this->timesMax || !$duration) {
                        $errors[] = 'time_range.invalid.duration';
                    }

                    if (!$booking->beginAfterMinStartDate(
                        $this->minStartDelay,
                        $this->minStartTimeDelay,
                        $this->getTimeUnitIsDay(),
                        $this->timeZone
                    )
                    ) {
                        $errors[] = 'time_range.invalid.min_start';
                    }
                }
            }

        }

        return $errors;
    }


    /**
     * Set all related booking amounts (booking amount, fee as asker and offerer)
     *
     * @param Booking $booking
     *
     * @return Booking
     */
    public function setBookingAmounts(Booking $booking)
    {
        if ($booking->getAmount() > 0) {
            //If VAT is not included in listing prices fixing then VAT amount is added here
            if (!$this->includeVat) {
                $booking->setAmount($booking->getAmount() + $booking->getAmount() * $this->vatRate);
            }
            //Amounts
            $booking->setAmount(round($booking->getAmount()));

            //Fees computation Asker
            $asker = $booking->getUser();
            $booking->setAmountFeeAsAsker($this->feeAsAsker * $booking->getAmount());
            //If user has a custom fee defined we use it
            if ($asker) {
                $feeAsAsker = $asker->getFeeAsAsker();
                if ($feeAsAsker || $feeAsAsker === 0) {
                    $booking->setAmountFeeAsAsker(($feeAsAsker / 100) * $booking->getAmount());
                }
            }

            //Fees computation Offerer
            $offerer = $booking->getListing()->getUser();
            $booking->setAmountFeeAsOfferer($this->feeAsOfferer * $booking->getAmount());
            //If user has a custom fee defined we use it
            if ($offerer) {
                $feeAsOfferer = $offerer->getFeeAsOfferer();
                if ($feeAsOfferer || $feeAsOfferer === 0) {
                    $booking->setAmountFeeAsOfferer(($feeAsOfferer / 100) * $booking->getAmount());
                }
            }

            //Round cents with decimal
            $booking->setAmountFeeAsAsker(round($booking->getAmountFeeAsAsker()));
            $booking->setAmountFeeAsOfferer(round($booking->getAmountFeeAsOfferer()));

            $booking->setAmountTotal($booking->getAmount() + $booking->getAmountFeeAsAsker());
        }

        return $booking;
    }

    /**
     * @param int    $askerId
     * @param string $locale
     * @param int    $page
     * @param array  $status
     *
     * @return Paginator
     */
    public function findByAsker($askerId, $locale, $page, $status = array())
    {
        $queryBuilder = $this->getRepository()->getFindByAskerQuery($askerId, $locale, $status);

        //Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();

        return new Paginator($query);
    }


    /**
     * @param int    $askerId
     * @param string $locale
     * @param int    $page
     * @param array  $status
     *
     * @return Paginator
     */
    public function findPayedByAsker($askerId, $locale, $page, $status = array())
    {
        $queryBuilder = $this->getRepository()->getFindByAskerQuery($askerId, $locale, $status);

        $queryBuilder
            ->andWhere('b.payedBookingAt IS NOT NULL');

        if ($this->mangopayIsEnabled()) {
            $queryBuilder
                ->andWhere('b.mangopayPayinPreAuthId IS NOT NULL');
        }

        //Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();

        return new Paginator($query);
    }


    /**
     * @param  int    $offererId
     * @param  string $locale
     * @param  int    $page
     * @param  array  $status
     * @return Paginator
     */
    public function findByOfferer($offererId, $locale, $page, $status = array())
    {
        $queryBuilder = $this->getRepository()->getFindByOffererQuery($offererId, $locale, $status);

        //Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();

        return new Paginator($query);
    }

    /**
     * @param int    $id
     * @param int    $askerId
     * @param string $locale
     * @param array  $status
     *
     * @return Booking|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByAsker($id, $askerId, $locale, $status = array())
    {
        $queryBuilder = $this->getRepository()->getFindOneByAskerQuery($id, $askerId, $locale, $status);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }


    /**
     * Create a new booking
     *
     * @param Booking $booking
     * @return Booking|false
     */
    public function create(Booking $booking)
    {
        if (in_array($booking->getStatus(), Booking::$newableStatus)) {
            //New Booking confirmation
            $booking->setStatus(Booking::STATUS_NEW);
            $booking->setNewBookingAt(new \DateTime());
            $booking = $this->save($booking);

            $this->mailer->sendBookingRequestMessageToOfferer($booking);
            $this->mailer->sendBookingRequestMessageToAsker($booking);

            if ($this->smser) {
                $this->smser->sendBookingRequestMessageToOfferer($booking);
            }

            return $booking;
        }

        return false;
    }

    /**
     * Alert Expiring Bookings
     *
     * @param int    $alertExpirationDelay
     * @param int    $expirationDelay
     * @param int    $acceptationDelay
     * @param string $timeZone
     *
     * @return integer
     */
    public function alertExpiringBookings(
        $alertExpirationDelay,
        $expirationDelay = null,
        $acceptationDelay = null,
        $timeZone = null
    ) {
        $result = 0;
        $expirationDelay = $expirationDelay !== null ? $expirationDelay : $this->expirationDelay;
        $acceptationDelay = $acceptationDelay !== null ? $acceptationDelay : $this->acceptationDelay;
        $timeZone = $timeZone !== null ? $timeZone : $this->getTimeZone();

        $bookingsExpiringToAlert = $this->getRepository()->findBookingsExpiringToAlert(
            $alertExpirationDelay,
            $expirationDelay,
            $acceptationDelay,
            $timeZone
        );

        foreach ($bookingsExpiringToAlert as $bookingExpiringToAlert) {
            if ($this->alertExpiring($bookingExpiringToAlert)) {
                $result++;
            }
        }

        return $result;
    }

    /**
     * Alert Expiring Booking
     *
     * @param Booking $booking
     *
     * @return bool
     */
    public function alertExpiring(Booking $booking)
    {
        if (in_array($booking->getStatus(), Booking::$expirableStatus)) {
            $booking->setAlertedExpiring(true);
            $booking = $this->save($booking);
            //Mail offerer
            $this->mailer->sendBookingExpirationAlertMessageToOfferer($booking);
            if ($this->smser) {
                $this->smser->sendBookingExpirationAlertMessageToOfferer($booking);
            }

            return true;
        }

        return false;
    }


    /**
     * Expire Bookings
     *
     * @param int    $expirationDelay
     * @param int    $acceptationDelay
     * @param string $timeZone
     *
     * @return integer
     */
    public function expireBookings($expirationDelay = null, $acceptationDelay = null, $timeZone = null)
    {
        $result = 0;
        $expirationDelay = $expirationDelay !== null ? $expirationDelay : $this->expirationDelay;
        $acceptationDelay = $acceptationDelay !== null ? $acceptationDelay : $this->acceptationDelay;
        $timeZone = $timeZone !== null ? $timeZone : $this->getTimeZone();

        $bookingsToExpire = $this->getRepository()->findBookingsToExpire(
            $expirationDelay,
            $acceptationDelay,
            $timeZone
        );

        foreach ($bookingsToExpire as $bookingToExpire) {
            if ($this->expire($bookingToExpire)) {
                $result++;
            }
        }

        return $result;
    }


    /**
     * Expire Booking
     *
     * @param Booking $booking
     *
     * @return bool
     */
    public function expire(Booking $booking)
    {
        if (in_array($booking->getStatus(), Booking::$expirableStatus)) {
            $booking->setStatus(Booking::STATUS_EXPIRED);
            $booking = $this->save($booking);

            $this->mailer->sendBookingRequestExpiredMessageToOfferer($booking);//Mail offerer
            $this->mailer->sendBookingRequestExpiredMessageToAsker($booking);//Mail asker

            if ($this->smser) {
                $this->smser->sendBookingRequestExpiredMessageToOfferer($booking);
                $this->smser->sendBookingRequestExpiredMessageToAsker($booking);
            }

            return true;
        }

        return false;
    }

    /**
     * Alert Imminent Bookings
     *
     * @param int $imminentDelay
     *
     * @return integer
     */
    public function alertImminentBookings($imminentDelay)
    {
        $result = 0;
        $bookingsImminentToAlert = $this->getRepository()->findBookingsImminentToAlert(
            $imminentDelay
        );
        foreach ($bookingsImminentToAlert as $bookingImminentToAlert) {
            if ($this->alertImminent($bookingImminentToAlert)) {
                $result++;
            }
        }

        return $result;
    }

    /**
     * Alert imminent Booking
     *
     * @param Booking $booking
     *
     * @return bool
     */
    public function alertImminent(Booking $booking)
    {
        if (in_array($booking->getStatus(), Booking::$validatableStatus)) {
            $booking->setAlertedImminent(true);
            $booking = $this->save($booking);

            $this->mailer->sendBookingImminentMessageToOfferer($booking);//Mail offerer
            $this->mailer->sendBookingImminentMessageToAsker($booking);//Mail asker

            if ($this->smser) {
                $this->smser->sendBookingImminentMessageToOfferer($booking);
                $this->smser->sendBookingImminentMessageToAsker($booking);
            }

            return true;
        }

        return false;
    }

    /**
     * Return whether a booking can be canceled by asker
     *
     * @param Booking $booking
     *
     * @return bool
     */
    public function canBeCanceledByAsker(Booking $booking)
    {
        $statusIsOk = in_array($booking->getStatus(), Booking::$cancelableStatus);
        $hasStarted = $booking->hasStarted();

        //todo: check if refund can be made with voucher amount
        if ($this->voucherIsEnabled()) {
            if ($booking->getAmountDiscountVoucher()) {
                return false;
            }
        }

        if ($statusIsOk && !$hasStarted && !$booking->isValidated()) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Return whether a booking can be accepted or refused by offerer
     * A booking can be accepted or refused no later than $acceptationDelay hours before it starts
     * and no later than $expirationDelay hours after new booking request date
     *
     * @param Booking $booking
     *
     * @return bool
     */
    public function canBeAcceptedOrRefusedByOfferer(Booking $booking)
    {
        //$refusableStatus is equal to $payableStatus
        $statusIsOk = in_array($booking->getStatus(), Booking::$payableStatus);

        $isNotExpired = $booking->getTimeBeforeExpiration(
            $this->expirationDelay,
            $this->acceptationDelay,
            $this->timeZone
        );
        $isNotExpired = $isNotExpired && $isNotExpired > 0;

        if ($statusIsOk && $isNotExpired) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Pay booking (when offerer accept)
     *
     *  Set listing availabilities status as booked
     *  Set booking status as payed
     *  Send mails
     *
     * @param Booking $booking
     *
     * @return Booking|bool
     */
    public function pay(Booking $booking)
    {
        $canBeAcceptedOrRefused = $this->canBeAcceptedOrRefusedByOfferer($booking);
        if ($canBeAcceptedOrRefused) {
            try {
                $event = new BookingEvent($booking);
                $this->dispatcher->dispatch(BookingEvents::BOOKING_PAY, $event);//Payment is done here
                $booking = $event->getBooking();

                $booking->setStatus(Booking::STATUS_PAYED);
                $booking->setPayedBookingAt(new \DateTime());
                $booking = $this->save($booking);

                $listing = $booking->getListing();

                $this->listingAvailabilityManager->saveAvailabilitiesStatus(
                    $listing->getId(),
                    new DateRange($booking->getStart(), $booking->getEnd()),
                    array(),
                    $this->timeUnitIsDay ? array() : array(
                        new TimeRange($booking->getStartTime(), $booking->getEndTime())
                    ),
                    ListingAvailability::STATUS_BOOKED,
                    $listing->getPrice(),
                    $this->endDayIncluded,
                    false
                );

                $this->mailer->sendBookingAcceptedMessageToAsker($booking);
                $this->mailer->sendBookingAcceptedMessageToOfferer($booking);

                if ($this->smser) {
                    $this->smser->sendBookingAcceptedMessageToAsker($booking);
                }

                //Refuse other booking requests existing in this booking date range
                $bookingsToRefuse = $this->getRepository()->findBookingsToRefuse(
                    $booking,
                    $this->endDayIncluded,
                    $this->timeUnitIsDay
                );
                foreach ($bookingsToRefuse as $bookingToRefuse) {
                    $this->refuse($bookingToRefuse);
                }

                return $booking;
            } catch (\Exception $e) {
//                throw new \Exception($e);

                //In case of error while payment for example
                return false;
            }
        }

        return false;
    }


    /**
     * Offerer refuse booking :
     *  Set booking status as refused
     *  Send mails
     *
     * @param Booking $booking
     *
     * @return Booking|bool
     */
    public function refuse(Booking $booking)
    {
        $canBeAcceptedOrRefused = $this->canBeAcceptedOrRefusedByOfferer($booking);
        if ($canBeAcceptedOrRefused) {
            $booking->setStatus(Booking::STATUS_REFUSED);
            $booking->setRefusedBookingAt(new \DateTime());
            $booking = $this->save($booking);

            $this->mailer->sendBookingRefusedMessageToAsker($booking);
            $this->mailer->sendBookingRefusedMessageToOfferer($booking);

            if ($this->smser) {
                $this->smser->sendBookingRefusedMessageToAsker($booking);
            }

            return $booking;
        }

        return false;
    }

    /**
     * Validate Booking. The offerer can be payed.
     * Is the booking object considered as validated (Offerer can be payed) after booking start date or booking end date.
     *
     * @param string $validatedMoment 'start' or 'end'*
     * @param int    $validatedDelay  Time after or before the moment the booking is considered as validated (in minutes)
     * @param string $timeZone        Default user time zone
     *
     * @return int
     */
    public function validateBookings($validatedMoment, $validatedDelay, $timeZone = null)
    {
        $result = 0;
        $timeZone = $timeZone !== null ? $timeZone : $this->getTimeZone();

        $bookingsToValidate = $this->getRepository()->findBookingsToValidate(
            $validatedMoment,
            $validatedDelay,
            $timeZone
        );
        foreach ($bookingsToValidate as $bookingToValidate) {
            if ($this->validate($bookingToValidate)) {
                $result++;
            }
        }

        return $result;
    }


    /**
     * Validate Booking:
     *  Transfer the funds from the asker's wallet to the offerer's wallet.
     *  The offerer can be payed.
     *  Platform fees are taken here.
     *
     * @param Booking $booking
     *
     * @return bool
     */
    public function validate(Booking $booking)
    {
        if (in_array($booking->getStatus(), Booking::$validatableStatus) && !$booking->isValidated()) {

            $event = new BookingValidateEvent($booking);
            $this->dispatcher->dispatch(BookingEvents::BOOKING_VALIDATE, $event);
            $booking = $event->getBooking();
            $validated = $event->getValidated();

            if ($validated) {
                $booking->setValidated(true);
                $booking = $this->save($booking);

                //Offerer nb bookings setting
                $offerer = $booking->getListing()->getUser();
                $offerer->setNbBookingsOfferer($offerer->getNbBookingsOfferer() + 1);
                $this->em->persist($offerer);
                $this->em->flush();

                //Asker nb bookings setting
                $asker = $booking->getUser();
                $asker->setNbBookingsAsker($asker->getNbBookingsAsker() + 1);
                $this->em->persist($asker);
                $this->em->flush();

                $this->dispatcher->dispatch(BookingEvents::BOOKING_POST_VALIDATE, $event);

                //Mail offerer
                $this->mailer->sendReminderToRateAskerMessageToOfferer($booking);
                //Mail asker
                $this->mailer->sendReminderToRateOffererMessageToAsker($booking);

                return true;
            } else {
                $booking->setStatus(Booking::STATUS_PAYMENT_REFUSED);
                $this->save($booking);
            }
        }

        return false;
    }



    /**
     * Asker cancel booking.
     *  There are two cases:
     *      Either the booking has not been accepted by the offerer and so not already payed. Its status is new and
     *          no refund need to be made.
     *      Either booking status is payed and is not already validated. In this case the funds are in the asker wallet
     *      and must be refunded to his bank account.
     *
     *
     * Operations:
     *  Optionally refund asker
     *  Set booking status as cancel
     *  Send mails
     *
     * @param Booking $booking
     *
     * @return Booking|bool
     */
    public function cancel(Booking $booking)
    {
        if ($this->canBeCanceledByAsker($booking)) {
            $cancelable = false;

            if ($booking->getStatus() == Booking::STATUS_PAYED) {
                $event = new BookingPayinRefundEvent($booking);
                $this->dispatcher->dispatch(BookingEvents::BOOKING_REFUND, $event);

                $booking = $event->getBooking();
                $cancelable = $event->getCancelable();
            } elseif ($booking->getStatus() == Booking::STATUS_NEW) {
                $cancelable = true;
            }

            if ($cancelable) {
                if ($booking->getStatus() == Booking::STATUS_PAYED) {
                    $listing = $booking->getListing();

                    //Free booking availabilities
                    $this->listingAvailabilityManager->saveAvailabilitiesStatus(
                        $listing->getId(),
                        new DateRange($booking->getStart(), $booking->getEnd()),
                        array(),
                        $this->timeUnitIsDay ? array() : array(
                            new TimeRange($booking->getStartTime(), $booking->getEndTime())
                        ),
                        ListingAvailability::STATUS_AVAILABLE,
                        $listing->getPrice(),
                        $this->endDayIncluded,
                        true
                    );
                }

                $booking->setStatus(Booking::STATUS_CANCELED_ASKER);
                $booking->setCanceledAskerBookingAt(new \DateTime());
                $booking = $this->save($booking);

                $this->mailer->sendBookingCanceledByAskerMessageToAsker($booking);
                $this->mailer->sendBookingCanceledByAskerMessageToOfferer($booking);

                if ($this->smser) {
                    $this->smser->sendBookingCanceledByAskerMessageToOfferer($booking);
                }


                return $booking;
            }
        }

        return false;
    }


    /**
     * @param  Booking $booking
     * @return Booking
     */
    public function save(Booking $booking)
    {
        $this->persistAndFlush($booking);

        return $booking;
    }

    /**
     * @return boolean
     */
    public function getTimeUnitIsDay()
    {
        return $this->timeUnitIsDay;
    }

    /**
     * @return int
     */
    public function getExpirationDelay()
    {
        return $this->expirationDelay;
    }


    /**
     * @return int
     */
    public function getAcceptationDelay()
    {
        return $this->acceptationDelay;
    }


    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @return TwigSwiftMailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return bool
     */
    public function mangopayIsEnabled()
    {
        return isset($this->bundles["CocoricoMangoPayBundle"]);
    }

    /**
     * @return bool
     */
    private function voucherIsEnabled()
    {
        return isset($this->bundles["CocoricoVoucherBundle"]);
    }


    /**
     *
     * @return BookingRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('CocoricoCoreBundle:Booking');
    }

    /**
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}

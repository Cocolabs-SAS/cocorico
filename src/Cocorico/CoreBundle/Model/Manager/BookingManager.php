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
use Cocorico\CoreBundle\Event\BookingEvent;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\CoreBundle\Event\BookingPayinRefundEvent;
use Cocorico\CoreBundle\Event\BookingValidateEvent;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\CoreBundle\Repository\BookingRepository;
use Cocorico\CoreBundle\Repository\ListingDiscountRepository;
use Cocorico\CoreBundle\Smser\TwigSmser;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BookingManager extends BaseManager
{
    protected $em;
    protected $dm;
    protected $feeAsAsker;
    protected $feeAsOfferer;
    protected $endDayIncluded;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $timesMax;
    protected $minStartDelay;
    protected $minStartTimeDelay;
    public $minPrice;
    protected $listingAvailabilityManager;
    public $maxPerPage;
    protected $mailer;
    protected $smser;
    protected $smsReceiver;
    /** @var \Cocorico\ListingOptionBundle\Model\Manager\OptionManager $optionManager */
    protected $optionManager;
    /** @var \Cocorico\VoucherBundle\Model\Manager\VoucherManager $voucherManager */
    public $voucherManager;
    protected $defaultListingStatus;
    protected $vatRate;
    protected $includeVat;
    protected $dispatcher;

    /**
     * @param EntityManager                                              $em
     * @param DocumentManager                                            $dm
     * @param float                                                      $feeAsAsker
     * @param float                                                      $feeAsOfferer
     * @param boolean                                                    $endDayIncluded
     * @param int                                                        $timeUnit App time unit in minutes
     * @param int                                                        $timesMax Max times unit if time_unit is not day
     * @param int                                                        $minStartDelay
     * @param int                                                        $minStartTimeDelay
     * @param int                                                        $minPrice
     * @param ListingAvailabilityManager                                 $listingAvailabilityManager
     * @param int                                                        $maxPerPage
     * @param TwigSwiftMailer                                            $mailer
     * @param TwigSmser                                                  $smser
     * @param                                                            $smsReceiver
     * @param                                                            $optionManager
     * @param                                                            $voucherManager
     * @param int                                                        $defaultListingStatus
     * @param float                                                      $vatRate
     * @param bool                                                       $includeVat
     * @param EventDispatcherInterface                                   $dispatcher
     */
    public function __construct(
        EntityManager $em,
        DocumentManager $dm,
        $feeAsAsker,
        $feeAsOfferer,
        $endDayIncluded,
        $timeUnit,
        $timesMax,
        $minStartDelay,
        $minStartTimeDelay,
        $minPrice,
        ListingAvailabilityManager $listingAvailabilityManager,
        $maxPerPage,
        TwigSwiftMailer $mailer,
        TwigSmser $smser,
        $smsReceiver,
        $optionManager,
        $voucherManager,
        $defaultListingStatus,
        $vatRate,
        $includeVat,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->dm = $dm;
        $this->feeAsAsker = $feeAsAsker;
        $this->feeAsOfferer = $feeAsOfferer;
        $this->endDayIncluded = $endDayIncluded;
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->timesMax = $timesMax;
        $this->minStartDelay = $minStartDelay;
        $this->minStartTimeDelay = $minStartTimeDelay;
        $this->minPrice = $minPrice;
        $this->listingAvailabilityManager = $listingAvailabilityManager;
        $this->maxPerPage = $maxPerPage;
        $this->mailer = $mailer;
        $this->smser = $smser;
        $this->smsReceiver = $smsReceiver;
        $this->optionManager = $optionManager;
        $this->voucherManager = $voucherManager;
        $this->defaultListingStatus = $defaultListingStatus;
        $this->vatRate = $vatRate;
        $this->includeVat = $includeVat;
        $this->dispatcher = $dispatcher;
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
        }

        if (!$this->timeUnitIsDay) {
            if ($timeRange && $timeRange->getStart() && $timeRange->getEnd()) {
                $booking->setStartTime($timeRange->getStart());
                $booking->setEndTime($timeRange->getEnd());
            }
        } else {
            $booking->setStartTime(new \DateTime('1970-01-01 00:00'));
            $booking->setEndTime(new \DateTime('1970-01-01 00:00'));
        }

        $booking->setCancellationPolicy($listing->getCancellationPolicy());

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

                    $startMinute = intval($booking->getStartTime()->getTimestamp() / 60);
                    $nbMinutes = intval(
                        ($booking->getEndTime()->getTimestamp() - $booking->getStartTime()->getTimestamp()) / 60
                    );
                    $nbMinutes += $startMinute;

                    //Price defined for each minute are defined for one time unit (hour, ...)
                    for ($k = $startMinute; $k < $nbMinutes; $k++) {
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
            //Discount
            /** @var ListingDiscountRepository $listingDiscountRepository */
            $listingDiscountRepository = $this->em->getRepository("CocoricoCoreBundle:ListingDiscount");
            $discount = $listingDiscountRepository->findOneByFromQuantity($listing->getId(), $bookingDuration);

            if ($discount) {
                $amount -= ($discount->getDiscount() / 100) * $amount;
            }

            if ($amount <= 0 || $amount < $this->minPrice) {
                $errors[] = 'amount_invalid';
            } else {
                /**
                 * Options
                 * Options amount is added to amount to pay by asker
                 * No discount on Options amount
                 * Fees are taken on options amount
                 *
                 */
                if ($this->optionIsEnabled() && $booking->getOptions()) {
                    $amountOptions = $this->optionManager->getBookingOptionsAmount($booking);
                    $booking->setAmountOptions($amountOptions);
                    $amount = $amount + $booking->getAmountOptions();
                }

                //Booking amount and fees are setted here
                $booking = $this->setBookingAmounts($booking, $amount);

                /**
                 * Voucher
                 * Voucher amount is substracted from amount to pay to asker.
                 *
                 * Fees still the same
                 */
                if ($this->voucherIsEnabled() && $booking->getCodeVoucher()) {
                    $result = $this->applyVoucherOnBooking($booking);
                    /** @var Booking $booking */
                    $booking = $result['booking'];
                    $errors[] = $result['error'];
//                    $amount = max($booking->getAmountToPayByAsker() - $booking->getAmountDiscountVoucher(), $this->minPrice);
//                    $booking = $this->setBookingAmounts($booking, $amount);
                }
            }
        }

        return $errors;
    }


    /**
     * Apply voucher on booking amount : Modify booking amount or return error type if any
     *
     * array['booking']              Booking
     *      ['error']                string ('amount_voucher_invalid' or 'voucher_invalid') in case of error
     *
     * @param Booking $booking
     * @return array (See above)
     */
    public function applyVoucherOnBooking(Booking $booking)
    {
        $result = array('booking' => $booking, 'error' => '');

        $bookingAmount = $booking->getAmountToPayByAsker();

        $voucher = $this->voucherManager->getRepository()->findValidVoucher(
            $booking->getCodeVoucher(),
            $bookingAmount
        );

        if ($voucher) {
            $discountAmount = $this->voucherManager->getDiscountAmount(
                $bookingAmount,
                $voucher->getDiscountType(),
                $voucher->getDiscount()
            );

            if ($discountAmount) {
                $amountToPay = $bookingAmount - $discountAmount;
                //Si le montant de la resa apres reduction est inferieur au prix minimum d'une resa
                //Le montant de la resa devient celui du prix miminum
                if ($amountToPay < $this->minPrice) {
//                    $amountToPay = $this->minPrice;
//                    $booking->setAmountFeeAsAsker(0);
//                    $booking->setAmountFeeAsOfferer(0);
                    $result['error'] = 'amount_voucher_invalid';
                } else {
                    //Si le montant de la reduction est superieur au montant des commissions demandeur
                    //le montant des commissions demandeur devient 0
                    //sinon le montant des commissions demandeur est egale a la commission demandeur moins le montant de la reduction
                    if ($discountAmount >= $booking->getAmountFeeAsAsker()) {
                        $booking->setAmountFeeAsAsker(0);
                    } else {
                        $booking->setAmountFeeAsAsker($booking->getAmountFeeAsAsker() - $discountAmount);
                    }

                    //Si le montant à payer est inferieur aux commissions offreur
                    //Alors le montant des commissions offreur devient 0
                    if ($amountToPay <= $booking->getAmountFeeAsOfferer()) {
//                        $booking->setAmountFeeAsOfferer(0);
                        $result['error'] = 'amount_voucher_invalid';
                    } else {
                        //
                    }
                }

                if (!$result['error']) {
                    $booking->setAmountTotal($amountToPay);
                    $booking->setAmountDiscountVoucher($bookingAmount - $amountToPay);
                    $booking->setDiscountVoucher($voucher->getDiscount());//Memorize discount voucher value to booking
                } else {
                    $booking->setAmountTotal(0);
                    $booking->setDiscountVoucher(null);
                    $booking->setAmountDiscountVoucher(null);
                }

//                $this->logBookingAmounts($booking);
            }
        } else {
            $result['error'] = 'code_voucher_invalid';
        }

        $result['booking'] = $booking;

        return $result;
    }


//    private function logBookingAmounts(Booking $booking)
//    {
//       echo "booking->amount:" . $booking->getAmount() . "<br>";
//        echo "booking->amountTotal:" . $booking->getAmountTotal() . "<br>";
//        echo "booking->amountFeeAsAsker:" . $booking->getAmountFeeAsAsker() . "<br>";
//        echo "booking->amountFeeAsOfferer:" . $booking->getAmountFeeAsOfferer() . "<br>";
//        echo "booking->amountDiscountVoucher:" . $booking->getAmountDiscountVoucher() . "<br>";
//    }

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

        $now = new \DateTime();
        if ($this->minStartDelay > 0) {
            $now->add(new \DateInterval('P' . $this->minStartDelay . 'D'));
        }

        if ($booking->getStart()) {
            $interval = $now->diff($booking->getStart())->format('%r%a');
            if ($interval < 0) {
                $errors[] = 'date_range.invalid.min_start';
            }

            $oneYearLater = $now->add(new \DateInterval('P1Y'));
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
                if ($booking->getStartTime() > $booking->getEndTime()) {
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

                    if (!$booking->hasCorrectStartTime(
                        $this->minStartDelay,
                        $this->minStartTimeDelay,
                        $this->getTimeUnitIsDay()
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
     * @param  int    $amount
     *
     * @return Booking
     */
    public function setBookingAmounts(Booking $booking, $amount)
    {
        if ($amount > 0) {
            //If VAT is not included in listing prices fixing then VAT amount is added here
            if (!$this->includeVat) {
                $amount += $amount * $this->vatRate;
            }

            //Amounts
            $booking->setAmount(round($amount));

            //Fees computation Asker
            $asker = $booking->getUser();
            $booking->setAmountFeeAsAsker($this->feeAsAsker * $amount);
            //If user has a custom fee defined we use it
            if ($asker) {
                $feeAsAsker = $asker->getFeeAsAsker();
                if ($feeAsAsker || $feeAsAsker === 0) {
                    $booking->setAmountFeeAsAsker(($feeAsAsker / 100) * $amount);
                }
            }

            //Fees computation Offerer
            $offerer = $booking->getListing()->getUser();
            $booking->setAmountFeeAsOfferer($this->feeAsOfferer * $amount);
            //If user has a custom fee defined we use it
            if ($offerer) {
                $feeAsOfferer = $offerer->getFeeAsOfferer();
                if ($feeAsOfferer || $feeAsOfferer === 0) {
                    $booking->setAmountFeeAsOfferer(($feeAsOfferer / 100) * $amount);
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

            //Voucher use
            if ($this->voucherIsEnabled()) {
                $this->voucherManager->checkVoucherUse($booking);
            }

            $this->mailer->sendBookingRequestMessageToOfferer($booking);
            $this->mailer->sendBookingRequestMessageToAsker($booking);

            $this->smser->sendBookingRequestMessageToOfferer($booking);

            return $booking;
        }

        return false;
    }

    /**
     * Alert Expiring Bookings
     *
     * @param int $expirationDelay
     * @param int $alertExpirationDelay
     *
     * @return integer
     */
    public function alertExpiringBookings($expirationDelay, $alertExpirationDelay)
    {
        $result = 0;
        $bookingsExpiringToAlert = $this->getRepository()->findBookingsExpiringToAlert(
            $expirationDelay,
            $alertExpirationDelay
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
            $this->smser->sendBookingExpirationAlertMessageToOfferer($booking);

            return true;
        }

        return false;
    }


    /**
     * Expire Bookings
     *
     * @param int $expirationDelay
     *
     * @return integer
     */
    public function expireBookings($expirationDelay)
    {
        $result = 0;
        $bookingsToExpire = $this->getRepository()->findBookingsToExpire($expirationDelay);
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
            //Mail offerer
            $this->mailer->sendBookingRequestExpiredMessageToOfferer($booking);
            $this->smser->sendBookingRequestExpiredMessageToOfferer($booking);
            //Mail asker
            $this->mailer->sendBookingRequestExpiredMessageToAsker($booking);
            $this->smser->sendBookingRequestExpiredMessageToAsker($booking);

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
            //Mail offerer
            $this->mailer->sendBookingImminentMessageToOfferer($booking);
            $this->smser->sendBookingImminentMessageToOfferer($booking);
            //Mail asker
            $this->mailer->sendBookingImminentMessageToAsker($booking);
            $this->smser->sendBookingImminentMessageToAsker($booking);

            return true;
        }

        return false;
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
        if (in_array($booking->getStatus(), Booking::$payableStatus)) {
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
                $this->smser->sendBookingAcceptedMessageToAsker($booking);

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
        if (in_array($booking->getStatus(), Booking::$refusableStatus)) {
            $booking->setStatus(Booking::STATUS_REFUSED);
            $booking->setRefusedBookingAt(new \DateTime());
            $booking = $this->save($booking);

            $this->mailer->sendBookingRefusedMessageToAsker($booking);
            $this->smser->sendBookingRefusedMessageToAsker($booking);

            $this->mailer->sendBookingRefusedMessageToOfferer($booking);


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
     *
     * @return int
     */
    public function validateBookings($validatedMoment, $validatedDelay)
    {
        $result = 0;
        $bookingsToValidate = $this->getRepository()->findBookingsToValidate(
            $validatedMoment,
            $validatedDelay
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

                //Voucher used
                if ($this->voucherIsEnabled()) {
                    $this->voucherManager->checkVoucherUsed($booking);
                }

                //Mail offerer
                $this->mailer->sendReminderToRateAskerMessageToOfferer($booking);
                //Mail asker
                $this->mailer->sendReminderToRateOffererMessageToAsker($booking);

                return true;
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
        if ($booking->canBeCanceledByAsker()) {
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
                    //Free booking availabilities
                    $this->listingAvailabilityManager->saveAvailabilitiesStatus(
                        $booking->getListing()->getId(),
                        new DateRange($booking->getStart(), $booking->getEnd()),
                        array(),
                        $this->timeUnitIsDay ? array() : array(
                            new TimeRange($booking->getStartTime(), $booking->getEndTime())
                        ),
                        ListingAvailability::STATUS_AVAILABLE,
                        $booking->getListing()->getPrice(),
                        $this->endDayIncluded,
                        true
                    );
                }

                $booking->setStatus(Booking::STATUS_CANCELED_ASKER);
                $booking->setCanceledAskerBookingAt(new \DateTime());
                $booking = $this->save($booking);

                $this->mailer->sendBookingCanceledByAskerMessageToAsker($booking);
                $this->mailer->sendBookingCanceledByAskerMessageToOfferer($booking);
                $this->smser->sendBookingCanceledByAskerMessageToOfferer($booking);

                return $booking;
            }
        }

        return false;
    }

    /**
     * Accept or refuse booking from sms response
     *
     * @return bool|int
     */
    public function acceptOrRefuseFromSMS()
    {
        $result = 0;

        //Get all last sms response
        $start = new \DateTime();
        $interval = new \DateInterval('PT1H');
        $start->sub($interval);
        $end = new \DateTime();
        $end->add($interval);
        $allSMS = $this->smsReceiver->receiveAll($start, $end);

        //SMS response
        foreach ($allSMS as $sms) {
            //Get one sms response
            $smsReceived = $this->smsReceiver->receiveOne($sms->getId());
            if ($smsReceived) {
                //tag is of form: [SMS type]-[User Id]-[Booking Id]. See CoreBundle/TwigSmser->sendBookingMessagesToOfferer
                $tag = explode("-", $smsReceived->getTag());
                if (count($tag) == 3) {
                    $smsType = $tag[0];
                    $userId = $tag[1];
                    $bookingId = $tag[2];

                    if ($smsType == "booking_request_offerer" && $userId && $bookingId) {
                        /** @var Booking $booking */
                        $booking = $this->getRepository()
                            ->findOneBy(
                                array(
                                    'id' => $bookingId,
                                    'status' => Booking::STATUS_NEW
                                )
                            );

                        //If SMS received is send from offerer
                        if ($booking && $booking->getListing()->getUser()->getId() == $userId) {
                            $answer = $smsReceived->getMessage();
                            $answer = trim($answer);
                            if (strtolower($answer) == "yes" || strtolower($answer) == "oui") {
                                if ($this->pay($booking)) {
                                    $result++;
                                }
                            } elseif (strtolower($answer) == "no" || strtolower($answer) == "non") {
                                if ($this->refuse($booking)) {
                                    $result++;
                                }
                            }
                        }
                    }
                }
            }
        }

        //Check SMS credits left
        $smsCreditLeft = $this->smsReceiver->getProvider()->checkSMSCreditsLeft();
        $now = new \DateTime();

        if ($smsCreditLeft && $smsCreditLeft < 30 && $now->format('H:i') == '10:00') {
            $this->mailer->sendMessageToAdmin(
                'SMS credits limit reached',
                'Hello, The number of remaining SMS is ' . $smsCreditLeft . '.'
            );
        }

        return $result;
    }

    /**
     * Add listing options to the corresponding booking
     *
     * @param Booking $booking
     * @param array   $locales
     * @param string  $locale
     *
     * @return Booking
     */
    public function setBookingOptions(Booking $booking, $locales, $locale)
    {
        if ($this->optionIsEnabled()) {
            /** @var \Cocorico\ListingOptionBundle\Repository\ListingOptionRepository $repo */
            $repo = $this->getEntityManager()->getRepository('CocoricoListingOptionBundle:ListingOption');
            $listingOptions = $repo->findByListing($booking->getListing()->getId(), $locale);

            foreach ($listingOptions as $i => $listingOption) {
                $option = new \Cocorico\ListingOptionBundle\Entity\BookingOption();
                $option->setBooking($booking);
                $option->setPrice($listingOption->getPrice());
                $option->setMin($listingOption->getMin());
                $option->setMax($listingOption->getMax());
                $option->setType($listingOption->getType());
                $option->setListingOptionId($listingOption->getId());

                foreach ($locales as $locale) {
                    /** @var \Cocorico\ListingOptionBundle\Entity\ListingOptionTranslation $translation */
                    $translation = $listingOption->getTranslations()->get($locale);
                    $option
                        ->translate($locale)
                        ->setName($translation->getName())
                        ->setDescription($translation->getDescription());
                }

                $option->mergeNewTranslations();

                $booking->addOption($option);
            }
        }

        return $booking;
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
        return $this->em->getClassMetadata('Cocorico\CoreBundle\Entity\Booking')->hasField('mangopayPayinPreAuthId');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function voucherIsEnabled()
    {
        $voucherIsEnabled = !$this->em->getMetadataFactory()->isTransient('Cocorico\VoucherBundle\Entity\Voucher');
        //Voucher and Option Bundles not ready yet for booking amount computing
        if ($voucherIsEnabled && $this->optionIsEnabled()) {
            throw new \Exception("Voucher and Option are enabled but not ready yet");
        }

        return $voucherIsEnabled;
    }

    /**
     * @return bool
     */
    public function optionIsEnabled()
    {
        return !$this->em->getMetadataFactory()->isTransient('Cocorico\ListingOptionBundle\Entity\ListingOption');
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

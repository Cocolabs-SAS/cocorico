<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Model\BaseBooking;
use Cocorico\CoreBundle\Model\BookingDepositRefundInterface;
use Cocorico\CoreBundle\Model\BookingOptionInterface;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\ReviewBundle\Entity\Review;
use Cocorico\TimeBundle\Model\DayTimeRange;
use Cocorico\UserBundle\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Booking
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\BookingRepository")
 *
 * @ORM\Table(name="booking",indexes={
 *    @ORM\Index(name="start_idx", columns={"start"}),
 *    @ORM\Index(name="end_idx", columns={"end"}),
 *    @ORM\Index(name="start_time_idx", columns={"start_time"}),
 *    @ORM\Index(name="end_time_idx", columns={"end_time"}),
 *    @ORM\Index(name="status_idx", columns={"status"}),
 *    @ORM\Index(name="validated_idx", columns={"validated"}),
 *    @ORM\Index(name="new_booking_at_idx", columns={"new_booking_at"}),
 *    @ORM\Index(name="alerted_expiring_idx", columns={"alerted_expiring"}),
 *    @ORM\Index(name="alerted_imminent_idx", columns={"alerted_imminent"}),
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"}),
 *    @ORM\Index(name="invoice_number_idx", columns={"invoice_number"}),
 *    @ORM\Index(name="refund_invoice_number_idx", columns={"refund_invoice_number"})
 *  })
 *
 */
class Booking extends BaseBooking
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     * @var integer
     */
    private $id;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="bookings", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    protected $user;


    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Listing", inversedBy="bookings")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $listing;


    /**
     * @ORM\OneToOne(targetEntity="Cocorico\MessageBundle\Entity\Thread", mappedBy="booking", cascade={"remove"}, orphanRemoval=true)
     */
    private $thread;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ReviewBundle\Entity\Review", mappedBy="booking", cascade={"remove"}, orphanRemoval=true)
     */
    private $reviews;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\CoreBundle\Entity\BookingBankWire", mappedBy="booking", cascade={"remove"}, orphanRemoval=true)
     *
     * @var BookingBankWire
     **/
    private $bankWire;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\CoreBundle\Entity\BookingPayinRefund", mappedBy="booking", cascade={"remove"}, orphanRemoval=true)
     *
     * @var BookingPayinRefund
     **/
    private $payinRefund;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Model\BookingOptionInterface", mappedBy="booking", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $options;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\CoreBundle\Entity\BookingUserAddress", mappedBy="booking", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userAddress;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\CoreBundle\Model\BookingDepositRefundInterface", mappedBy="booking", cascade={"remove"}, orphanRemoval=true)
     **/
    private $depositRefund;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param User|null $user
     * @return Booking
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user address
     *
     * @param BookingUserAddress $userAddress
     * @return Booking
     */
    public function setUserAddress($userAddress)
    {
        $userAddress->setBooking($this);
        $this->userAddress = $userAddress;

        return $this;
    }

    /**
     * Get booking user address
     *
     * @return BookingUserAddress
     */
    public function getUserAddress()
    {
        return $this->userAddress;
    }

    /**
     * Set listing
     *
     * @param Listing $listing
     * @return Booking
     */
    public function setListing(Listing $listing)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * Get listing
     *
     * @return Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param Thread $thread
     */
    public function setThread($thread)
    {
        $thread->setBooking($this);
        $this->thread = $thread;
    }

    /**
     * @return Review[]|ArrayCollection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param ArrayCollection|Review[] $reviews
     */
    public function setReviews(ArrayCollection $reviews)
    {
        foreach ($reviews as $review) {
            $review->setBooking($this);
        }

        $this->reviews = $reviews;
    }

    /**
     * @return BookingBankWire
     */
    public function getBankWire()
    {
        return $this->bankWire;
    }

    /**
     * @param BookingBankWire $bankWire
     */
    public function setBankWire($bankWire)
    {
        $this->bankWire = $bankWire;
    }

    /**
     * @return BookingPayinRefund
     */
    public function getPayinRefund()
    {
        return $this->payinRefund;
    }

    /**
     * @param BookingPayinRefund $payinRefund
     */
    public function setPayinRefund($payinRefund)
    {
        $this->payinRefund = $payinRefund;
    }

    /**
     * Compute the number of time units (days, hours, ...) from booking dates
     * If we are not in time unit day mode, the duration is equal to the number of days multiplied
     * by the number of time units.
     *
     * @param boolean $endDayIncluded
     * @param int     $timeUnit
     *
     * @return int|bool  nb time units
     */
    public function getDuration($endDayIncluded, $timeUnit)
    {
        if (!$this->getStart() || !$this->getEnd()) {
            return false;
        }

        $timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $durationDay = $this->getDateRange()->getDuration($endDayIncluded);

        if ($durationDay < 1) {
            return false;
        }

        if ($timeUnitIsDay) {//Duration in time unit (day)
            $duration = $durationDay;
        } else {//Duration in time unit (hour, ...)
            if (!$this->getStartTime() || !$this->getEndTime()) {
                return false;
            }
            $durationTime = $this->getTimeRange()->getDuration($timeUnit);
            $duration = $durationDay * $durationTime;
        }

        return $duration;
    }

    /**
     * Get time in seconds before booking request expire depending on its status
     *
     * @param int $expirationDelay  in minutes
     * @param int $acceptationDelay in minutes
     *
     * @return bool|int nb seconds before expiration
     */
    public function getTimeBeforeExpiration($expirationDelay, $acceptationDelay)
    {
        switch ($this->getStatus()) {
            case self::STATUS_DRAFT:
                return false;
                break;
            case self::STATUS_NEW:
                $expirationDate = $this->getExpirationDate($expirationDelay, $acceptationDelay);
                if ($expirationDate) {
                    $now = new DateTime('now');

                    return round($expirationDate->getTimestamp() - $now->getTimestamp());
                }

                return false;

                break;
            default:
                //No expiration case
                return false;
        }
    }

    /**
     * Get booking expiration date:
     *   Equal to the smallest date between (new booking date + expiration delay) and (booking start date + acceptation delay)
     *
     * @param int $bookingExpirationDelay  in minutes
     * @param int $bookingAcceptationDelay in minutes
     *
     * @return Datetime|bool (in UTC)
     */
    public function getExpirationDate($bookingExpirationDelay, $bookingAcceptationDelay)
    {
        if ($this->getNewBookingAt()) {
            $expirationDate = clone $this->getNewBookingAt();
            $expirationDate->add(new DateInterval('PT'.$bookingExpirationDelay.'M'));

            $acceptationDate = clone $this->getStart();
            $acceptationDate->sub(new DateInterval('PT'.$bookingAcceptationDelay.'M'));

            //Return minus date
            if ($expirationDate->format('Ymd H:i') < $acceptationDate->format('Ymd H:i')) {
                return $expirationDate;
            } else {
                return $acceptationDate;
            }
        }

        return false;
    }


    /**
     * Get booking validation date.
     * This is the date when the booking is considered as validated (started, or finished; ... ) according to the
     * cocorico.booking.validated_moment and cocorico.booking.validated_delay parameters.
     * At this moment the offerer can be payed.
     *
     * @param string $bookingValidationMoment ("start"|"end")
     * @param int    $bookingValidationDelay  in minutes
     *
     * @return Datetime|bool (in UTC)
     */
    public function getValidationDate($bookingValidationMoment, $bookingValidationDelay)
    {
        $methodName = "get" . ucfirst($bookingValidationMoment);
        /** @var DateTime $validatedAt */
        $validatedAt = $this->$methodName();
        if ($validatedAt) {
            $validatedAtCloned = clone $validatedAt;
            if ($bookingValidationDelay >= 0) {
                $validatedAtCloned->add(new DateInterval('PT'.$bookingValidationDelay.'M'));
            } else {
                $validatedAtCloned->sub(new DateInterval('PT'.abs($bookingValidationDelay).'M'));
            }

            return $validatedAtCloned;
        }

        return false;
    }


    /**
     * Get time in seconds before booking start
     *
     * @return bool|int nb seconds before start
     */
    public function getTimeBeforeStart()
    {
        if ($this->getStart()) {
            $now = new DateTime('now');

            return $this->getStart()->getTimestamp() - $now->getTimestamp();
        }

        return false;
    }

    /**
     * Return whether a booking has started or not
     *
     * @return bool
     */
    public function hasStarted()
    {
        $now = new DateTime();

        return ($this->getStart()->format('Ymd') <= $now->format('Ymd'));
    }


    /**
     * Check if booking begin during or after the minimum start date time according to $minStartTimeDelay
     * old: hasCorrectStartTime
     *
     * @param int $minStartTimeDelay in minutes
     * @return bool
     */
    public function beginDuringOrAfterMinStartDate($minStartTimeDelay)
    {
        $minStartTime = new DateTime();
        $minStartTime->add(new DateInterval('PT'.$minStartTimeDelay.'M'));

        return $this->getStart()->format('Ymd H:i') >= $minStartTime->format('Ymd H:i');
    }


    /**
     * Check if booking begin after the maximum acceptable date according to $acceptationDelay
     *
     * @param int $acceptationDelay in minutes
     * @return bool
     */
    public function beginAfterMaxAcceptableDate($acceptationDelay)
    {
        $maxAcceptableDate = new DateTime();
        $maxAcceptableDate->add(new DateInterval('PT'.$acceptationDelay.'M'));

        return $this->getStart()->format('Ymd') > $maxAcceptableDate->format('Ymd');
    }


    /**
     * Check if two booking overlap their dates time ranges
     *
     * @param Booking $booking
     * @param  bool   $endDayIncluded
     * @return bool
     */
    public function overlap(Booking $booking, $endDayIncluded)
    {
        $timeRanges = $this->getDateTimeRange()->getDaysTimeRanges($endDayIncluded);
        $timeRangesToCheck = $booking->getDateTimeRange()->getDaysTimeRanges($endDayIncluded);

        return DayTimeRange::overlap($timeRanges, $timeRangesToCheck);
    }


    /**
     * Get bookings overlapping this booking
     *
     * @param Booking[] $bookings
     * @param bool      $endDayIncluded
     * @return Booking[]
     */
    public function getOverlapping($bookings, $endDayIncluded)
    {
        $result = array();

        foreach ($bookings as $index => $booking) {
            if ($this->overlap($booking, $endDayIncluded) && $this->getId() != $booking->getId()) {
                $result[] = $booking;
            }
        }

        return $result;
    }

    /**
     * Add BookingOption
     *
     * @param  BookingOptionInterface $option
     * @return Booking
     */
    public function addOption($option)
    {
        if (!$this->options->contains($option)) {
            $option->setBooking($this);
            $this->options->add($option);
        }

        return $this;
    }

    /**
     * Remove BookingOption
     *
     * @param BookingOptionInterface $option
     */
    public function removeOption($option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get BookingOptions
     *
     * @return Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ArrayCollection $options
     * @return $this
     */
    public function setOptions(ArrayCollection $options)
    {
        foreach ($options as $option) {
            $option->setBooking($this);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @return BookingDepositRefundInterface
     */
    public function getDepositRefund()
    {
        return $this->depositRefund;
    }

    /**
     * @param BookingDepositRefundInterface $depositRefund
     */
    public function setDepositRefund($depositRefund)
    {
        $this->depositRefund = $depositRefund;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId() . " (" . $this->getListing() . ":" . $this->getStart()->format('d-m-Y') . ")";
    }
}

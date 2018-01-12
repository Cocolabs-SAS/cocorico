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

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseBooking
 *
 * @CocoricoAssert\Booking(groups={"new"})
 *
 * @ORM\MappedSuperclass()
 *
 *
 */
abstract class BaseBooking
{
    /* Status */
    const STATUS_DRAFT = 0;
    const STATUS_NEW = 1;
//    const STATUS_ACCEPTED = 2;
    const STATUS_PAYED = 3;
    const STATUS_EXPIRED = 4;
    const STATUS_REFUSED = 5;
    const STATUS_CANCELED_ASKER = 6;
//    const STATUS_CANCELED_OFFERER = 7;
    const STATUS_PAYMENT_REFUSED = 8;

    public static $statusValues = array(
        self::STATUS_DRAFT => 'entity.booking.status.draft',
        self::STATUS_NEW => 'entity.booking.status.new',
//        self::STATUS_ACCEPTED => 'entity.booking.status.accepted',
        self::STATUS_PAYED => 'entity.booking.status.payed',
        self::STATUS_EXPIRED => 'entity.booking.status.expired',
        self::STATUS_REFUSED => 'entity.booking.status.refused',
        self::STATUS_CANCELED_ASKER => 'entity.booking.status.canceled_asker',
//        self::STATUS_CANCELED_OFFERER => 'entity.booking.status.canceled_offerer',
        self::STATUS_PAYMENT_REFUSED => 'entity.booking.status.payment_refused'
    );

    public static $visibleStatus = array(
        self::STATUS_NEW,
//        self::STATUS_ACCEPTED => 'entity.booking.status.accepted',
        self::STATUS_PAYED,
        self::STATUS_EXPIRED,
        self::STATUS_REFUSED,
        self::STATUS_CANCELED_ASKER,
//        self::STATUS_CANCELED_OFFERER,
        self::STATUS_PAYMENT_REFUSED
    );

    //Status relative to a valid transaction
    public static $payedStatus = array(
        self::STATUS_PAYED,
    );

    //Status for which booking can be created
    public static $newableStatus = array(
        self::STATUS_DRAFT
    );

    //Status for which booking can be canceled by asker
    public static $cancelableStatus = array(
        self::STATUS_NEW,
        self::STATUS_PAYED
    );

    //Status for which booking can be expired
    public static $expirableStatus = array(
        self::STATUS_DRAFT,
        self::STATUS_NEW
    );

    //Status for which booking can be payed
    public static $payableStatus = array(
        self::STATUS_NEW
    );

    //Status for which booking can be refused
    public static $refusableStatus = array(
        self::STATUS_NEW
    );

    //Status for which booking can be validated
    public static $validatableStatus = array(
        self::STATUS_PAYED
    );


    /**
     * @ORM\Column(name="start", type="datetime")
     * @Assert\NotBlank(message="assert.not_blank")
     * @var \DateTime
     */
    protected $start;

    /**
     * @ORM\Column(name="end", type="datetime")
     * @Assert\NotBlank(message="assert.not_blank")
     * @var \DateTime
     */
    protected $end;

    /**
     * @ORM\Column(name="start_time", type="datetime")
     *
     * @var \DateTime
     */
    protected $startTime;

    /**
     * @ORM\Column(name="end_time", type="datetime")
     *
     * @var \DateTime
     */
    protected $endTime;

    /**
     * @ORM\Column(name="status", type="smallint")
     *
     * @var integer
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * @ORM\Column(name="validated", type="boolean")
     *
     * @var boolean
     */
    protected $validated = false;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=8, scale=0)
     *
     * Total booking amount without asker fee (booking amount for all days - discounts  )
     *
     * @var integer
     */
    protected $amount;


    /**
     * @ORM\Column(name="amount_fee_as_asker", type="decimal", precision=8, scale=0)
     *
     * @var integer
     */
    protected $amountFeeAsAsker;

    /**
     * @ORM\Column(name="amount_fee_as_offerer", type="decimal", precision=8, scale=0)
     *
     * @var integer
     */
    protected $amountFeeAsOfferer;

    /**
     * @ORM\Column(name="amount_total", type="decimal", precision=8, scale=0)
     *
     * Total booking amount for asker (booking amount for all days - discounts + asker fee )
     *
     * @var integer
     */
    protected $amountTotal;


    /**
     *
     * @ORM\Column(name="cancellation_policy", type="smallint")
     *
     * @var integer
     */
    protected $cancellationPolicy;


    /**
     * @ORM\Column(name="new_booking_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $newBookingAt;

    /**
     * @ORM\Column(name="payed_booking_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $payedBookingAt;

    /**
     * @ORM\Column(name="refused_booking_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $refusedBookingAt;


    /**
     * @ORM\Column(name="canceled_asker_booking_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $canceledAskerBookingAt;

    /**
     * @ORM\Column(name="alerted_expiring", type="boolean")
     *
     * @var boolean
     */
    protected $alertedExpiring = false;

    /**
     * @ORM\Column(name="alerted_imminent", type="boolean")
     *
     * @var boolean
     */
    protected $alertedImminent = false;


    /**
     * Initial booking message
     *
     * @ORM\Column(name="message", type="text", length=65535, nullable=false)
     *
     * @var string
     */
    protected $message;

    public function __construct()
    {

    }

    /**
     * Return visible status values
     *
     * @return array
     */
    public static function getVisibleStatusValues()
    {
        $status = array_intersect_key(
            self::$statusValues,
            array_flip(self::$visibleStatus)
        );

        return $status;
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
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param \DateTime $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return \DateTime
     */
    public function getStartDateAndTime()
    {
        $start = $this->getStart()->format('Y-m-d');
        if ($this->getStartTime()) {
            $start .= ' ' . $this->getStartTime()->format('H:i:s');
        }

        return new \DateTime($start);
    }

    /**
     * Set status
     *
     * @param  integer $status
     * @return BaseBooking
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for booking.status : %s.', $status)
            );
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusText()
    {
        return self::$statusValues[$this->getStatus()];
    }


    /**
     * Set amount
     *
     * @param int $amount
     * @return Booking
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Amount including offerer fee and excluding asker fee.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Amount excluding VAT
     *
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountExcludingVAT($vatRate)
    {
        return $this->amount / (1 + $vatRate);
    }

    /**
     * Amount Decimal excluding VAT
     *
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountExcludingVATDecimal($vatRate)
    {
        return $this->getAmountExcludingVAT($vatRate) / 100;
    }

    /**
     * @param int $amountTotal
     */
    public function setAmountTotal($amountTotal)
    {
        $this->amountTotal = $amountTotal;
    }

    /**
     * Total booking amount for asker include asker fee and offerer fee:
     *  = booking amount for all days - discounts + asker fee + offerer fee
     *
     * @return int
     */
    public function getAmountTotal()
    {
        return $this->amountTotal;
    }

    /**
     * Amount total excluding VAT
     *
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountTotalExcludingVAT($vatRate)
    {
        return ($this->amountTotal / (1 + $vatRate));
    }

    /**
     * Amount total Decimal excluding VAT
     *
     * @param float $vatRate
     *
     * @return float
     */
    public function getAmountTotalExcludingVATDecimal($vatRate)
    {
        return $this->getAmountTotalExcludingVAT($vatRate) / 100;
    }


    /**
     * Total VAT Amount
     *
     * @param float $vatRate
     *
     * @return float
     */
    public function getAmountTotalVATDecimal($vatRate)
    {
        return $this->getAmountTotalDecimal() - $this->getAmountTotalExcludingVATDecimal($vatRate);
    }

    /**
     * Booking amount excluding asker and offerer fees:
     *  = booking amount for all days - discounts
     *
     * @return int
     */
    public function getAmountExcludingFees()
    {
        return $this->getAmount() - $this->getAmountFeeAsOfferer();
    }


    /**
     *
     * @return int
     */
    public function getAmountExcludingFeesDecimal()
    {
        return $this->getAmountExcludingFees() / 100;
    }

    /**
     * @return int
     */
    public function getAmountFeeAsAsker()
    {
        return $this->amountFeeAsAsker;
    }

    /**
     * Return asker fees + offerer fees
     *
     * @return int
     */
    public function getAmountTotalFee()
    {
        return $this->amountFeeAsAsker + $this->amountFeeAsOfferer;
    }


    /**
     * @param int $amountFeeAsAsker
     */
    public function setAmountFeeAsAsker($amountFeeAsAsker)
    {
        $this->amountFeeAsAsker = $amountFeeAsAsker;
    }

    /**
     * @return int
     */
    public function getAmountFeeAsOfferer()
    {
        return $this->amountFeeAsOfferer;
    }

    /**
     * @param int $amountFeeAsOfferer
     */
    public function setAmountFeeAsOfferer($amountFeeAsOfferer)
    {
        $this->amountFeeAsOfferer = $amountFeeAsOfferer;
    }


    /**
     * Total booking amount in cents to pay by asker
     * Alias of getAmountTotal
     *
     * @return int
     */
    public function getAmountToPayByAsker()
    {
        return $this->getAmountTotal();
    }

    /**
     * Total booking amount to pay by asker
     * Alias of getAmountTotalDecimal
     *
     * @return int
     */
    public function getAmountToPayByAskerDecimal()
    {
        return $this->getAmountTotalDecimal();
    }

    /**
     * Total booking amount in cents to pay to offerer
     *
     * @return int
     */
    public function getAmountToPayToOfferer()
    {
        return $this->getAmount() - $this->getAmountFeeAsOfferer();
    }

    /**
     * Total booking amount to pay to offerer
     *
     * @return int
     */
    public function getAmountToPayToOffererDecimal()
    {
        return ($this->getAmountDecimal() - $this->getAmountFeeAsOffererDecimal());
    }

    /**
     * Total booking amount excluding vat to pay to offerer
     *
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountToPayToOffererExcludingVAT($vatRate)
    {
        return $this->getAmountToPayToOfferer() / (1 + $vatRate);

    }


    /**
     * Total booking amount decimal excluding vat to pay to offerer
     *
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountToPayToOffererExcludingVATDecimal($vatRate)
    {
        return $this->getAmountToPayToOffererExcludingVAT($vatRate) / 100;

    }

    /**
     * Get amount decimal
     *
     * @return float
     */
    public function getAmountDecimal()
    {
        return $this->amount / 100;
    }

    /**
     * Get amount decimal
     *
     * @return float
     */
    public function getAmountTotalDecimal()
    {
        return $this->amountTotal / 100;
    }

    /**
     * Get amount fee  decimal
     *
     * @return float
     */
    public function getAmountFeeAsAskerDecimal()
    {
        return $this->amountFeeAsAsker / 100;
    }

    /**
     * Get amount fee  decimal
     *
     * @return float
     */
    public function getAmountFeeAsOffererDecimal()
    {
        return $this->amountFeeAsOfferer / 100;
    }

    /**
     * @return int
     */
    public function getCancellationPolicy()
    {
        return $this->cancellationPolicy;
    }

    /**
     * Get Cancellation Policy Text.
     *
     * @return string
     */
    public function getCancellationPolicyText()
    {
        return Listing::$cancellationPolicyValues[$this->getCancellationPolicy()];
    }

    /**
     * Get Cancellation Policy Description
     *
     * @return string
     */
    public function getCancellationPolicyDescription()
    {
        return Listing::$cancellationPolicyDescriptions[$this->getCancellationPolicy()];
    }


    /**
     * @param int $cancellationPolicy
     */
    public function setCancellationPolicy($cancellationPolicy)
    {
        if (!in_array($cancellationPolicy, array_keys(Listing::$cancellationPolicyValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for booking.cancellationPolicy : %s.', $cancellationPolicy)
            );
        }

        $this->cancellationPolicy = $cancellationPolicy;
    }

    /**
     * @return \DateTime
     */
    public function getNewBookingAt()
    {
        return $this->newBookingAt;
    }

    /**
     * @param \DateTime $newBookingAt
     */
    public function setNewBookingAt($newBookingAt)
    {
        $this->newBookingAt = $newBookingAt;
    }

    /**
     * @return \DateTime
     */
    public function getPayedBookingAt()
    {
        return $this->payedBookingAt;
    }

    /**
     * @param \DateTime $payedBookingAt
     */
    public function setPayedBookingAt($payedBookingAt)
    {
        $this->payedBookingAt = $payedBookingAt;
    }

    /**
     * @return \DateTime
     */
    public function getRefusedBookingAt()
    {
        return $this->refusedBookingAt;
    }

    /**
     * @param \DateTime $refusedBookingAt
     */
    public function setRefusedBookingAt($refusedBookingAt)
    {
        $this->refusedBookingAt = $refusedBookingAt;
    }

    /**
     * @return \DateTime
     */
    public function getCanceledAskerBookingAt()
    {
        return $this->canceledAskerBookingAt;
    }

    /**
     * @param \DateTime $canceledAskerBookingAt
     */
    public function setCanceledAskerBookingAt($canceledAskerBookingAt)
    {
        $this->canceledAskerBookingAt = $canceledAskerBookingAt;
    }

    /**
     * @return boolean
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @param boolean $validated
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
    }

    /**
     * @return boolean
     */
    public function isAlertedExpiring()
    {
        return $this->alertedExpiring;
    }

    /**
     * @param boolean $alertedExpiring
     */
    public function setAlertedExpiring($alertedExpiring)
    {
        $this->alertedExpiring = $alertedExpiring;
    }

    /**
     * @return boolean
     */
    public function isAlertedImminent()
    {
        return $this->alertedImminent;
    }

    /**
     * @param boolean $alertedImminent
     */
    public function setAlertedImminent($alertedImminent)
    {
        $this->alertedImminent = $alertedImminent;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

}

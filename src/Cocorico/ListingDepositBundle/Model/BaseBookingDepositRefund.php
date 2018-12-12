<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\ListingDepositBundle\Model;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseBookingDepositRefund
 *
 *
 * @ORM\MappedSuperclass()
 *
 */
abstract class BaseBookingDepositRefund
{
    /* Status */
    const STATUS_TO_DO = 0;
    const STATUS_NOTHING_TODO = 1;
    const STATUS_PAYED = 2;
    const STATUS_FAILED = 3;

    public static $statusValues = array(
        self::STATUS_TO_DO => 'entity.booking.deposit_refund.status.to_do',
        self::STATUS_NOTHING_TODO => 'entity.booking.deposit_refund.status.nothing_to_do',
        self::STATUS_PAYED => 'entity.booking.deposit_refund.status.payed',
        self::STATUS_FAILED => 'entity.booking.deposit_refund.status.failed',
    );

    /**
     * @ORM\Column(name="status_asker", type="smallint")
     *
     * @var integer
     */
    protected $statusAsker = self::STATUS_TO_DO;

    /**
     * @ORM\Column(name="status_offerer", type="smallint")
     *
     * @var integer
     */
    protected $statusOfferer = self::STATUS_TO_DO;
    /**
     *
     * @ORM\Column(name="amount", type="decimal", precision=8, scale=0)
     *
     * @var integer
     */
    protected $amount;

    /**
     *
     * @ORM\Column(name="amount_asker", type="decimal", precision=8, scale=0, nullable=true)
     *
     * @var integer
     */
    protected $amountAsker;

    /**
     *
     * @ORM\Column(name="amount_offerer", type="decimal", precision=8, scale=0, nullable=true)
     *
     * @var integer
     */
    protected $amountOfferer;

    /**
     * @ORM\Column(name="offerer_payed_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $offererPayedAt;

    /**
     * @ORM\Column(name="asker_payed_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $askerPayedAt;

    public function __construct()
    {

    }


    /**
     * Set status asker
     *
     * @param  integer $status
     * @return BaseBookingDepositRefund
     */
    public function setStatusAsker($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for booking_deposit_refund.status : %s.', $status)
            );
        }

        $this->statusAsker = $status;

        return $this;
    }


    /**
     * Get status
     *
     * @return integer
     */
    public function getStatusAsker()
    {
        return $this->statusAsker;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusAskerText()
    {
        return self::$statusValues[$this->getStatusAsker()];
    }


    /**
     * Set status offerer
     *
     * @param  integer $status
     * @return BaseBookingDepositRefund
     */
    public function setStatusOfferer($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for booking_deposit_refund.status : %s.', $status)
            );
        }

        $this->statusOfferer = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatusOfferer()
    {
        return $this->statusOfferer;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusOffererText()
    {
        return self::$statusValues[$this->getStatusOfferer()];
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
     * Get amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
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
     * @return \DateTime
     */
    public function getOffererPayedAt()
    {
        return $this->offererPayedAt;
    }

    /**
     * @param \DateTime $offererPayedAt
     */
    public function setOffererPayedAt($offererPayedAt)
    {
        $this->offererPayedAt = $offererPayedAt;
    }

    /**
     * @return \DateTime
     */
    public function getAskerPayedAt()
    {
        return $this->askerPayedAt;
    }

    /**
     * @param \DateTime $askerPayedAt
     */
    public function setAskerPayedAt($askerPayedAt)
    {
        $this->askerPayedAt = $askerPayedAt;
    }


    /**
     * @return int
     */
    public function getAmountAsker()
    {
        return $this->amountAsker;
    }

    /**
     * @return float
     */
    public function getAmountAskerDecimal()
    {
        return $this->amountAsker / 100;
    }

    /**
     * @param int $amountAsker
     */
    public function setAmountAsker($amountAsker)
    {
        $this->amountAsker = $amountAsker;
    }

    /**
     * @return int
     */
    public function getAmountOfferer()
    {
        return $this->amountOfferer;
    }

    /**
     * @return float
     */
    public function getAmountOffererDecimal()
    {
        return $this->amountOfferer / 100;
    }

    /**
     * @param int $amountOfferer
     */
    public function setAmountOfferer($amountOfferer)
    {
        $this->amountOfferer = $amountOfferer;
    }


    /**
     * Return whether the deposit amount to pay or to refund has been allocated between asker and / or offerer
     *
     * @return bool
     */
    public function amountsAreAllocated()
    {
        return $this->getAmount() == $this->getAmountAsker() + $this->getAmountOfferer();
    }

}

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

use Doctrine\ORM\Mapping as ORM;

/**
 * BaseBookingPayinRefund
 *
 *
 * @ORM\MappedSuperclass()
 *
 */
abstract class BaseBookingPayinRefund
{
    /* Status */
    const STATUS_PAYED = 1;

    public static $statusValues = array(
        self::STATUS_PAYED => 'entity.booking.bank_wire.status.payed',
    );

    /**
     * @ORM\Column(name="status", type="smallint")
     *
     * @var integer
     */
    protected $status = self::STATUS_PAYED;

    /**
     *
     * @ORM\Column(name="amount", type="decimal", precision=8, scale=0)
     *
     * @var integer
     */
    protected $amount;

    /**
     * @ORM\Column(name="payed_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $payedAt;

    public function __construct()
    {

    }


    /**
     * Set status
     *
     * @param  integer $status
     * @return BaseBookingPayinRefund
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for booking_payin_refund.status : %s.', $status)
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
     * @return $this
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
    public function getPayedAt()
    {
        return $this->payedAt;
    }

    /**
     * @param \DateTime $payedAt
     */
    public function setPayedAt($payedAt)
    {
        $this->payedAt = $payedAt;
    }
}

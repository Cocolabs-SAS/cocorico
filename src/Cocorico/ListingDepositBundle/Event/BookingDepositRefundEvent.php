<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Event;

use Cocorico\ListingDepositBundle\Entity\BookingDepositRefund;
use Symfony\Component\EventDispatcher\Event;

class BookingDepositRefundEvent extends Event
{
    protected $bookingDepositRefund;
    protected $checked;

    public function __construct(BookingDepositRefund $bookingDepositRefund)
    {
        $this->bookingDepositRefund = $bookingDepositRefund;
        $this->checked = false;
    }

    /**
     * @return BookingDepositRefund
     */
    public function getBookingDepositRefund()
    {
        return $this->bookingDepositRefund;
    }

    /**
     * @param BookingDepositRefund $bookingDepositRefund
     */
    public function setBookingDepositRefund(BookingDepositRefund $bookingDepositRefund)
    {
        $this->bookingDepositRefund = $bookingDepositRefund;
    }

    /**
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

}

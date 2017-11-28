<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Event;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\ListingDiscount;
use Symfony\Component\EventDispatcher\Event;

class BookingAmountEvent extends Event
{
    protected $booking;
    protected $discount;

    /**
     * @param Booking         $booking
     * @param ListingDiscount $discount If a listing discount exists for the current booking, its amount will be applied
     */
    public function __construct(Booking $booking, ListingDiscount $discount = null)
    {
        $this->booking = $booking;
        $this->discount = $discount;
    }

    /**
     * @return Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param Booking $booking
     */
    public function setBooking(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * @return ListingDiscount
     */
    public function getDiscount()
    {
        return $this->discount;
    }


}

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

interface BookingOptionInterface
{
    /**
     * @param Booking $booking
     * @return mixed
     */
    public function setBooking(Booking $booking);

    /**
     * @return Booking
     */
    public function getBooking();
}
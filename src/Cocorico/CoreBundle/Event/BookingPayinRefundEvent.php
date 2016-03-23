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

class BookingPayinRefundEvent extends BookingEvent
{
    protected $cancelable;

    public function __construct(Booking $booking)
    {
        parent::__construct($booking);
        $this->cancelable = false;
    }

    /**
     * @param bool $cancelable
     */
    public function setCancelable($cancelable)
    {
        $this->cancelable = $cancelable;
    }

    /**
     * @return bool
     */
    public function getCancelable()
    {
        return $this->cancelable;
    }

}

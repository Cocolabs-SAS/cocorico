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


class BookingDepositRefundEvents
{
    /**
     * The BOOKING_DEPOSIT_REFUND_CHECK event occurs when a booking deposit refund is checked.
     *
     * This event allows you to set the booking deposit refund status.
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingDepositRefundEvent instance.
     */
    const BOOKING_DEPOSIT_REFUND_CHECK = 'cocorico.booking_deposit_refund.check';
}
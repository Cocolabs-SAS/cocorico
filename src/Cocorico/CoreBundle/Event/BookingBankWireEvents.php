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


class BookingBankWireEvents
{
    /**
     * The BOOKING_BANK_WIRE_CHECK event occurs when a booking bank wire is checked.
     *
     * This event allows you to set the booking bank wire status.
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingBankWireEvent instance.
     */
    const BOOKING_BANK_WIRE_CHECK = 'cocorico.booking_bank_wire.check';
}
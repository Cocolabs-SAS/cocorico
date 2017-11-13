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


class BookingEvents
{
    /**
     * The BOOKING_INIT event occurs when a booking booking is initialized.
     *
     * This event allows you to set the response instead of using the default one.
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingEvent instance.
     */
    const BOOKING_INIT = 'cocorico.booking_new.init';

    /**
     * The BOOKING_NEW_SUBMITTED event occurs when the new booking form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingEvent instance.
     */
    const BOOKING_NEW_SUBMITTED = 'cocorico.booking_new.submitted';

    /**
     * The BOOKING_NEW_CREATED event occurs after new booking has been created with status new.
     *
     * This event allows you to do things after a new booking has been successfully created.
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingEvent instance.
     */
    const BOOKING_NEW_CREATED = 'cocorico.booking_new.created';


    /**
     * The BOOKING_PAY event occurs when a new booking has to be payed.
     *
     * This event allows you to charge the user.
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingEvent instance.
     */
    const BOOKING_PAY = 'cocorico.booking.pay';


    /**
     * The BOOKING_REFUND event occurs when a booking is canceled and has to be eventually refunded.
     *
     * This event allows you to refund the user.
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingPayinRefundEvent instance.
     */
    const BOOKING_REFUND = 'cocorico.booking.refund';

    /**
     * The BOOKING_VALIDATE event occurs when a booking is considered as done (started or finished).
     *
     * This event allows you to do what you want when the booking is considered as done (offerer payment, ...).
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingValidateEvent instance.
     */
    const BOOKING_VALIDATE = 'cocorico.booking.validate';

    /**
     * The BOOKING_POST_VALIDATE event occurs when a booking has been considered as done (started or finished).
     *
     * This event allows you to do what you want when the booking has been considered as done (offerer payment, ...).
     * The event listener method receives a Cocorico\CoreBundle\Event\BookingValidateEvent instance.
     */
    const BOOKING_POST_VALIDATE = 'cocorico.booking.post_validate';
}
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


class BookingFormEvents
{
    /**
     * The BOOKING_NEW_FORM_BUILD event is thrown each time a new booking form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\BookingFormBuilderEvent instance.
     */
    const BOOKING_NEW_FORM_BUILD = 'cocorico.booking_new.form.build';


    /**
     * The BOOKING_NEW_FORM_PROCESS event is thrown each time a new booking form is processed
     *
     * This event allows you to process and validate extra form fields.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\BookingFormEvent instance.
     */
    const BOOKING_NEW_FORM_PROCESS = 'cocorico.booking_new.form.process';
}
<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Smser;

use Cocorico\CoreBundle\Entity\Booking;

interface SmserInterface
{

    /**
     * Send a sms to offerer to confirm the new booking request
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRequestMessageToOfferer(Booking $booking);

    /**
     * SMS is sent 2 hours before a reservation request expires.
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingExpirationAlertMessageToOfferer(Booking $booking);

    /**
     * Send a sms to offerer to inform the new booking expiration
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRequestExpiredMessageToOfferer(Booking $booking);


    /**
     * SMS is sent if the asker cancels his reservation
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingCanceledByAskerMessageToOfferer(Booking $booking);

    /**
     * SMS is sent 24 hours before the booking begins
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingImminentMessageToOfferer(Booking $booking);


    /**
     * Send a sms to asker to inform the booking acceptation
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingAcceptedMessageToAsker(Booking $booking);


    /**
     * Send a sms to asker to inform the booking refusal
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRefusedMessageToAsker(Booking $booking);

    /**
     * Send a sms to asker to inform the new booking expiration
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRequestExpiredMessageToAsker(Booking $booking);

    /**
     * SMS is sent when the offerer rejects a reservation request
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingImminentMessageToAsker(Booking $booking);


}

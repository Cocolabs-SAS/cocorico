<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Mailer;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;

interface MailerInterface
{
    /**
     * email is sent when a listing is activated
     *
     * @param Listing $listing
     *
     * @return void
     */
    public function sendListingActivatedMessageToOfferer(Listing $listing);

    /**
     * Send an email to offerer to confirm the new booking request
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRequestMessageToOfferer(Booking $booking);

    /**
     * Send an email to offerer to inform the booking acceptation
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingAcceptedMessageToOfferer(Booking $booking);

    /**
     * email is sent when the bank refuses the payment
     *
     * @param Booking $booking
     *
     * @return void
     */
    //public function sendPaymentErrorMessageToOfferer(Booking $booking);

    /**
     * email is sent when the offerer rejects a reservation request
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRefusedMessageToOfferer(Booking $booking);

    /**
     * email is sent 2 hours before a reservation request expires.
     *
     * @param Booking $booking
     * @return void
     */
    public function sendBookingExpirationAlertMessageToOfferer(Booking $booking);

    /**
     * Send an email to offerer to inform the new booking expiration
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRequestExpiredMessageToOfferer(Booking $booking);

    /**
     * email is sent 24 hours after the end of the reservation.
     * so, that the offerer can rate the asker.
     *
     * @param Booking $booking
     * @return void
     */
    public function sendReminderToRateAskerMessageToOfferer(Booking $booking);

    /**
     * email is sent if the asker cancels his reservation
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingCanceledByAskerMessageToOfferer(Booking $booking);

    /**
     * email is sent 24 hours before the booking begins
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingImminentMessageToOfferer(Booking $booking);

    /**
     * email is sent when money is wired to the offerer
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendWireTransferMessageToOfferer(Booking $booking);

    /**
     * This is a reminder email that is sent every 27th day of the month.
     * It is sent only to users that have an active listing.
     *
     * @param Listing $listing
     *
     * @return void
     */
    public function sendUpdateYourCalendarMessageToOfferer(Listing $listing);

    /**
     * Send an email to asker to confirm the new booking request
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRequestMessageToAsker(Booking $booking);

    /**
     * Send an email to asker to inform the booking acceptation
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingAcceptedMessageToAsker(Booking $booking);

    /**
     * email is sent when the bank refuses the payment
     *
     * @param Booking $booking
     *
     * @return void
     */
    //public function sendPaymentErrorMessageToAsker(Booking $booking);

    /**
     * Send an email to asker to inform the booking refusal
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRefusedMessageToAsker(Booking $booking);

    /**
     * Send an email to asker to inform the new booking expiration
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingRequestExpiredMessageToAsker(Booking $booking);

    /**
     * email is sent when the offerer rejects a reservation request
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingImminentMessageToAsker(Booking $booking);

    /**
     * email is sent 24 hours after the end of the reservation.
     * so, that the asker can rate the offerer.
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendReminderToRateOffererMessageToAsker(Booking $booking);

    /**
     * email is sent if the asker cancels his reservation.
     *
     * @param Booking $booking
     *
     * @return void
     */
    public function sendBookingCanceledByAskerMessageToAsker(Booking $booking);

    /**
     * email is sent to admin
     *
     * @param string $subject
     * @param string $message
     *
     * @return void
     */
    public function sendMessageToAdmin($subject, $message);
}

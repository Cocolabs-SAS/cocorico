<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Form\Handler\Dashboard;

use Cocorico\CoreBundle\Entity\Booking;
use Symfony\Component\Form\Form;

/**
 * Handle Asker Dashboard Booking Form
 *
 */
class BookingAskerFormHandler extends BookingFormHandler
{
    /**
     * Save Booking.
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -2: Wrong Booking Status
     * -3: Refund error
     * -4: Unknown error
     */
    protected function onSuccess(Form $form)
    {
        $result = -4; //Unknown error

        /** @var Booking $booking */
        $booking = $form->getData();
        $message = $form->get("message")->getData();
        $this->threadManager->addReplyThread($booking, $message, $booking->getUser());
        //Cancel
        $type = $this->request->get('type');
        if (in_array($booking->getStatus(), Booking::$cancelableStatus)) {
            if ($type == 'cancel') {
                if ($this->bookingManager->cancel($booking)) {
                    $result = 1;
                } else {
                    $result = -3;
                }
            }
        } else {
            $result = -2;
        }

        return $result;
    }
}

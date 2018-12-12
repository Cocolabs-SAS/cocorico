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

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Event\BookingAmountEvent;
use Cocorico\CoreBundle\Event\BookingAmountEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class BookingAmountSubscriber implements EventSubscriberInterface
{

    private $minPrice;

    /**
     * @param int $minPrice
     */
    public function __construct($minPrice)
    {
        $this->minPrice = $minPrice;
    }

    /**
     * Add listing deposit amount to booking total amount: Modify booking amount or throw error if any
     *
     * Booking amount is added to amount to pay by asker.
     * Fees still the same
     *
     * @param BookingAmountEvent $event
     * @throws \Exception
     */
    public function onBookingPostAmountsSetting(BookingAmountEvent $event)
    {
        $booking = $event->getBooking();
        $listing = $booking->getListing();

        if ($listing->getAmountDeposit()) {
            try {
                $amountToPay = $booking->getAmountToPayByAsker() + $listing->getAmountDeposit();

                if ($amountToPay < $this->minPrice) {
                    throw new \Exception('amount_deposit_invalid');
                } else {
                    $booking->setAmountTotal($amountToPay);
                    $booking->setAmountDeposit($listing->getAmountDeposit());
                    $event->setBooking($booking);
                }

            } catch (\Exception $e) {
                $booking->setAmountTotal(0);
                $booking->setAmountDeposit($listing->getAmountDeposit());
                $event->setBooking($booking);
                throw new \Exception($e->getMessage());
            }
        }
    }

    private function logBookingAmounts(Booking $booking)
    {
        echo "booking->amount:" . $booking->getAmount() . "<br>";
        echo "booking->amountTotal:" . $booking->getAmountTotal() . "<br>";
        echo "booking->amountFeeAsAsker:" . $booking->getAmountFeeAsAsker() . "<br>";
        echo "booking->amountFeeAsOfferer:" . $booking->getAmountFeeAsOfferer() . "<br>";
        echo "booking->amountDeposit:" . $booking->getAmountDeposit() . "<br>";
    }

    public static function getSubscribedEvents()
    {
        //priority is after voucher bundle
        return array(
            BookingAmountEvents::BOOKING_POST_AMOUNTS_SETTING => array('onBookingPostAmountsSetting', 80),
        );
    }
}
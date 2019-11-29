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
use Cocorico\CoreBundle\Entity\BookingPayinRefund;
use Cocorico\CoreBundle\Model\Manager\BookingPayinRefundManager;
use DateTime;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingPayinRefundSubscriber implements EventSubscriberInterface
{
    protected $bookingPayinRefundManager;
    protected $entityManager;

    public function __construct(BookingPayinRefundManager $bookingPayinRefundManager)
    {
        $this->entityManager = $bookingPayinRefundManager->getEntityManager();
        $this->bookingPayinRefundManager = $bookingPayinRefundManager;
    }

    /**
     * Refund booking amount to asker when it's canceled
     *
     * @param BookingPayinRefundEvent  $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     * @throws Exception
     */
    public function onBookingRefund(BookingPayinRefundEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $booking = $event->getBooking();
        if ($booking->getStatus() == Booking::STATUS_PAYED) {
            //Get fees and refund amount
            $feeAndAmountToRefund = $this->bookingPayinRefundManager->getFeeAndAmountToRefundToAsker($booking);

            //If there is something to refund to asker
            if ($feeAndAmountToRefund["refund_amount"]) { //$feeAndAmountToRefund["fee_to_collect_while_refund"] ||
                $payinRefund = new BookingPayinRefund();
                $payinRefund->setBooking($booking);
                $payinRefund->setAmount($feeAndAmountToRefund["refund_amount"]);
                $payinRefund->setUser($booking->getUser());
                $payinRefund->setPayedAt(new DateTime());
                $this->bookingPayinRefundManager->save($payinRefund);
                $this->entityManager->refresh($booking);

                $event->setCancelable(true);
            } elseif ($feeAndAmountToRefund["refund_percent"] == 0) {//nothing to refund to asker.
                $event->setCancelable(true);
            } else {
                //should not happen
            }
        }

        $event->setBooking($booking);
        $event->stopPropagation();
    }

    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_REFUND => array('onBookingRefund', 1),
        );
    }

}
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

use Cocorico\ListingDepositBundle\Entity\BookingDepositRefund;
use Cocorico\ListingDepositBundle\Model\Manager\BookingDepositRefundManager;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingDepositRefundSubscriber implements EventSubscriberInterface
{
    protected $bookingDepositRefundManager;
    protected $logger;

    /**
     * @param BookingDepositRefundManager $bookingDepositRefundManager
     * @param Logger                      $logger
     */
    public function __construct(BookingDepositRefundManager $bookingDepositRefundManager, Logger $logger)
    {
        $this->bookingDepositRefundManager = $bookingDepositRefundManager;
        $this->logger = $logger;
    }

    /**
     * Check the booking deposit payin_refund and payout status.
     * By default checking result is true because there is no payment system and all payment related things are
     * simulated.
     *
     * @param BookingDepositRefundEvent $event
     *
     * @return bool
     * @throws \Exception
     */
    public function onBookingDepositRefundCheck(BookingDepositRefundEvent $event)
    {
        $bookingDepositRefund = $event->getBookingDepositRefund();

        $this->getLogger()->debug(
            'BookingDepositRefundEvent checking :' .
            '|-BookingDepositRefund Id:' . $bookingDepositRefund->getId()
        );

        if ($bookingDepositRefund->amountsAreAllocated()) {
            if ($bookingDepositRefund->getAmountAsker() > 0) {
                $bookingDepositRefund->setStatusAsker(BookingDepositRefund::STATUS_PAYED);
                $bookingDepositRefund->setAskerPayedAt(new \DateTime());
            } else {
                $bookingDepositRefund->setStatusAsker(BookingDepositRefund::STATUS_NOTHING_TODO);
            }

            if ($bookingDepositRefund->getAmountOfferer() > 0) {
                $bookingDepositRefund->setStatusOfferer(BookingDepositRefund::STATUS_PAYED);
                $bookingDepositRefund->setOffererPayedAt(new \DateTime());
            } else {
                $bookingDepositRefund->setStatusOfferer(BookingDepositRefund::STATUS_NOTHING_TODO);
            }

            $bookingDepositRefund = $this->bookingDepositRefundManager->save($bookingDepositRefund);
//        $this->bookingDepositRefundManager->getMailer()->sendWireTransferMessageToOfferer($bookingBankWire->getBooking());
            $event->setChecked(true);
            $event->setBookingDepositRefund($bookingDepositRefund);
        }

        $event->stopPropagation();
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BookingDepositRefundEvents::BOOKING_DEPOSIT_REFUND_CHECK => array('onBookingDepositRefundCheck', 1),
        );
    }

}
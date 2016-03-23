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

use Cocorico\CoreBundle\Entity\BookingBankWire;
use Cocorico\CoreBundle\Model\Manager\BookingBankWireManager;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingBankWireSubscriber implements EventSubscriberInterface
{
    protected $bookingBankWireManager;
    protected $logger;

    /**
     * @param BookingBankWireManager $bookingBankWireManager
     * @param Logger                 $logger
     */
    public function __construct(BookingBankWireManager $bookingBankWireManager, Logger $logger)
    {
        $this->bookingBankWireManager = $bookingBankWireManager;
        $this->logger = $logger;
    }

    /**
     * Check the booking bank wire status.
     * By default checking result is true because there is no payment system and all payment related things are
     * simulated.
     *
     * @param BookingBankWireEvent $event
     *
     * @return bool
     * @throws \Exception
     */
    public function onBookingBankWireCheck(BookingBankWireEvent $event)
    {
        $bookingBankWire = $event->getBookingBankWire();

        $this->getLogger()->debug(
            'BookingBankWireManager Transaction Payed:' .
            '|-BookingBankWire Id:' . $bookingBankWire->getId()
        );

        $bookingBankWire->setStatus(BookingBankWire::STATUS_PAYED);
        $bookingBankWire->setPayedAt(new \DateTime());
        $bookingBankWire = $this->bookingBankWireManager->save($bookingBankWire);
        $this->bookingBankWireManager->getMailer()->sendWireTransferMessageToOfferer($bookingBankWire->getBooking());

        $event->setChecked(true);
        $event->setBookingBankWire($bookingBankWire);
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
            BookingBankWireEvents::BOOKING_BANK_WIRE_CHECK => array('onBookingBankWireCheck', 1),
        );
    }

}
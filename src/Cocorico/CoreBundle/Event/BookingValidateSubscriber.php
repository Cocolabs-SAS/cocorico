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

use Cocorico\CoreBundle\Model\Manager\BookingBankWireManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingValidateSubscriber implements EventSubscriberInterface
{
    protected $bookingBankWireManager;

    /**
     * @param BookingBankWireManager $bookingBankWireManager
     */
    public function __construct(BookingBankWireManager $bookingBankWireManager)
    {
        $this->bookingBankWireManager = $bookingBankWireManager;
    }

    /**
     * Transfer funds from asker to offerer wallet
     *
     * @param BookingValidateEvent $event
     * @return bool
     * @throws \Exception
     */
    public function onBookingValidate(BookingValidateEvent $event)
    {
        $booking = $event->getBooking();
        $bankWire = $this->bookingBankWireManager->create($booking);
        $this->bookingBankWireManager->save($bankWire);

        $event->setValidated(true);
        $event->setBooking($booking);
        $event->stopPropagation();
    }


    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_VALIDATE => array('onBookingValidate', 1),
        );
    }

}
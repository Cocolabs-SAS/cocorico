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

use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class BookingSubscriber implements EventSubscriberInterface
{
    protected $bookingManager;
    protected $dispatcher;

    /**
     * @param BookingManager           $bookingManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(BookingManager $bookingManager, EventDispatcherInterface $dispatcher)
    {
        $this->bookingManager = $bookingManager;
        $this->dispatcher = $dispatcher;
    }


    /**
     * Create a new booking
     *
     * @param BookingEvent $event
     */
    public function onBookingNewSubmitted(BookingEvent $event)
    {
        $booking = $this->bookingManager->create($event->getBooking());
        if ($booking) {
            $event->setBooking($booking);
            $this->dispatcher->dispatch(BookingEvents::BOOKING_NEW_CREATED, $event);
        }
    }


    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_NEW_SUBMITTED => array('onBookingNewSubmitted', 0),
        );
    }

}
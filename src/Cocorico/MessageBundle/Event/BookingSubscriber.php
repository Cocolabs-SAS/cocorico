<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Event;


use Cocorico\CoreBundle\Event\BookingEvent;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\MessageBundle\Model\ThreadManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class BookingSubscriber implements EventSubscriberInterface
{
    /**
     * @var ThreadManager
     */
    protected $threadManager;

    /**
     * @var TwigSwiftMailer
     */
    protected $twigSwiftMailer;

    /**
     * BookingSubscriber constructor.
     * @param ThreadManager $threadManager
     * @param TwigSwiftMailer $twigSwiftMailer
     */
    public function __construct(ThreadManager $threadManager, TwigSwiftMailer $twigSwiftMailer)
    {
        $this->threadManager = $threadManager;
        $this->twigSwiftMailer = $twigSwiftMailer;
    }


    /**
     * @param BookingEvent $event
     */
    public function onBookingNewCreated(BookingEvent $event)
    {
        $booking = $event->getBooking();
        $user = $booking->getUser();
        $this->threadManager->createNewListingThread($user, $booking);
        $this->twigSwiftMailer->sendMessageToAdmin("Subject booking", "Booking done!!!");
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_NEW_CREATED => array('onBookingNewCreated', 1),
        );
    }

}
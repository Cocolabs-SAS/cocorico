<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Model\Manager;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\ListingDepositBundle\Entity\BookingDepositRefund;
use Cocorico\ListingDepositBundle\Event\BookingDepositRefundEvent;
use Cocorico\ListingDepositBundle\Event\BookingDepositRefundEvents;
use Cocorico\ListingDepositBundle\Repository\BookingDepositRefundRepository;
use Cocorico\ListingDepositBundle\Repository\BookingRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BookingDepositRefundManager extends BaseManager
{
    protected $em;
    protected $mailer;
    protected $dispatcher;
    protected $timeZone;

    /**
     * @param EntityManager            $em
     * @param TwigSwiftMailer          $mailer
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        EntityManager $em,
        TwigSwiftMailer $mailer,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;

        //Parameters
        $parameters = $parameters["parameters"];
        $this->timeZone = $parameters["cocorico_time_zone"];
    }

    /**
     * Create a new booking deposit
     *
     * @param Booking $booking
     * @return BookingDepositRefund
     */
    public function create(Booking $booking)
    {
        $bookingDeposit = new BookingDepositRefund();
        $bookingDeposit->setBooking($booking);
        $bookingDeposit->setAmount($booking->getAmountDeposit());
        $bookingDeposit->setAsker($booking->getUser());
        $bookingDeposit->setOfferer($booking->getListing()->getUser());

        return $bookingDeposit;
    }

    /**
     * Generate bookings deposit refund. The asker deposit can be refunded if there is no litigious at the end of booking.
     *
     * @param int    $depositRefundDelay Time after the end of the booking from which the deposit refund can be generated (in minutes)
     * @param string $timeZone           Default user time zone
     *
     * @return int
     */
    public function generateBookingsDepositRefund($depositRefundDelay, $timeZone = null)
    {
        $result = 0;
        $timeZone = $timeZone !== null ? $timeZone : $this->getTimeZone();

        $bookingsDepositToRefund = $this->getBookingRepository()->findBookingsDepositToRefund(
            $depositRefundDelay,
            $timeZone
        );

        foreach ($bookingsDepositToRefund as $bookingDepositToRefund) {
            if ($this->generateBookingDepositRefund($bookingDepositToRefund)) {
                $result++;
            }
        }

        return $result;
    }


    /**
     * Generate Booking deposit refund:
     *  Refund asker deposit at the end of the booking.
     *  No fees on booking deposit refunding.
     *
     * @param Booking $booking
     *
     * @return bool
     */
    public function generateBookingDepositRefund(Booking $booking)
    {
        if (in_array($booking->getStatus(), Booking::$validatableStatus) && $booking->isValidated()
            && !$booking->getDepositRefund()
        ) {
            $bookingDepositRefund = $this->create($booking);
            $this->save($bookingDepositRefund);

            return true;
        }

        return false;
    }


    /**
     * Check Bookings Deposit refunds
     *
     * @return int
     */
    public function checkBookingsDepositRefunds()
    {
        $result = 0;
        $bookingsDepositRefunds = $this->getRepository()->findBookingsDepositRefundsToCheck();
        foreach ($bookingsDepositRefunds as $bookingDepositRefund) {
            if ($this->check($bookingDepositRefund)) {
                $result++;
            }
        }

        return $result;
    }


    /**
     * Check Bookings deposit refund:
     *  If the deposit refunds has been refunded to asker and/or payed to offerer the status is set to Done.
     *
     * @param BookingDepositRefund $bookingDepositRefund
     *
     * @return boolean
     */
    public function check(BookingDepositRefund $bookingDepositRefund)
    {
        $event = new BookingDepositRefundEvent($bookingDepositRefund);
        $this->dispatcher->dispatch(BookingDepositRefundEvents::BOOKING_DEPOSIT_REFUND_CHECK, $event);

        return $event->getChecked();
    }

    /**
     * @return TwigSwiftMailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }


    /**
     *
     * @return \Cocorico\ListingDepositBundle\Repository\BookingRepository
     */
    public function getBookingRepository()
    {
        $class = $this->em->getClassMetadata('CocoricoCoreBundle:Booking');

        return new BookingRepository($this->em, $class);
    }

    /**
     * @param  BookingDepositRefund $bookingDepositRefund
     *
     * @return BookingDepositRefund
     */
    public function save(BookingDepositRefund $bookingDepositRefund)
    {
        $this->persistAndFlush($bookingDepositRefund);

        return $bookingDepositRefund;
    }


    /**
     *
     * @return BookingDepositRefundRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('CocoricoListingDepositBundle:BookingDepositRefund');
    }


}

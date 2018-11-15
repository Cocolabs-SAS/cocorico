<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Event\BookingFormEvent;
use Cocorico\CoreBundle\Event\BookingFormEvents;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Booking Form
 *
 */
class BookingFormHandler
{
    protected $request;
    protected $flashBag;
    protected $bookingManager;
    protected $dispatcher;

    /**
     * @param RequestStack             $requestStack
     * @param BookingManager           $bookingManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RequestStack $requestStack,
        BookingManager $bookingManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->bookingManager = $bookingManager;
        $this->dispatcher = $dispatcher;
    }


    /**
     * Init booking
     *
     * @param User|null  $user
     * @param Listing    $listing
     * @param  \DateTime $start     format yyyy-mm-dd
     * @param  \DateTime $end       format yyyy-mm-dd
     * @param  \DateTime $startTime format H:i
     * @param  \DateTime $endTime   format H:i
     *
     * @return Booking $booking
     */
    public function init(
        $user,
        Listing $listing,
        \DateTime $start = null,
        \DateTime $end = null,
        \DateTime $startTime = null,
        \DateTime $endTime = null
    ) {
        $dateRange = $timeRange = null;
        if ($start && $end) {
            $dateRange = new DateRange($start, $end);
        }
        if ($startTime && $endTime) {
            $timeRange = new TimeRange(
                new \DateTime('1970-01-01 ' . $startTime->format('H:i')),
                new \DateTime('1970-01-01 ' . $endTime->format('H:i'))
            );
        }

        //Get date range from post request if any
        if ($this->request->getMethod() == 'POST') {
            $dateRange = DateRange::createFromArray($this->request->request->get('date_range'));
            $timeRange = TimeRange::createFromArray($this->request->request->get('time_range'));
        }

        $booking = $this->bookingManager->initBooking($listing, $user, $dateRange, $timeRange);

        return $booking;
    }

    /**
     * Process form
     *
     * @param $form
     *
     * @return int equal to :
     * 4: Options success
     * 3: Delivery success
     * 2: Voucher code success
     * 1: Success
     * 0: if form is not submitted:
     * -1: if form is not valid
     * -2: Self booking error
     * -3: Voucher error on code
     * -4: Voucher error on booking amount
     * -5: the max delivery distance has been reached
     * -6: distance matrix api error
     */
    public function process(Form $form)
    {
//        var_dump($success);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST')) {
            if (count($this->dispatcher->getListeners(BookingFormEvents::BOOKING_NEW_FORM_PROCESS)) > 0) {
                try {
                    $event = new BookingFormEvent($form);
                    $this->dispatcher->dispatch(BookingFormEvents::BOOKING_NEW_FORM_PROCESS, $event);
                    $result = $event->getResult();
                    if ($result !== false) {
                        return $result;
                    }
                } catch (\Exception $e) {

                }
            }

            if ($form->isValid()) {
                $result = $this->onSuccess($form);
            } else {
                $result = -1;//form not valid
            }
        } else {

            $result = 0; //Not submitted
        }

        return $result;
    }

    /**
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -2: Self booking error
     */
    private function onSuccess(Form $form)
    {
        /** @var Booking $booking */
        $booking = $form->getData();

        //No self booking
        if ($booking->getUser() == $booking->getListing()->getUser()) {
            $result = -2;

            return $result;
        }

        return 1;
    }

}
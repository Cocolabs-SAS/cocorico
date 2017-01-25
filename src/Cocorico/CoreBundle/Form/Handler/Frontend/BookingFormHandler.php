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
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Form\Handler\RegistrationFormHandler;
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
    protected $registrationHandler;

    /**
     * @param RequestStack            $requestStack
     * @param BookingManager          $bookingManager
     * @param RegistrationFormHandler $registrationHandler
     */
    public function __construct(
        RequestStack $requestStack,
        BookingManager $bookingManager,
        RegistrationFormHandler $registrationHandler
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->bookingManager = $bookingManager;
        $this->registrationHandler = $registrationHandler;
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
            $dateRangeParameter = $this->request->request->get('date_range');
            if (isset($dateRangeParameter['start']) && isset($dateRangeParameter['end'])) {
                $start = \DateTime::createFromFormat('d/m/Y', $dateRangeParameter['start']);
                $end = \DateTime::createFromFormat('d/m/Y', $dateRangeParameter['end']);
                $dateRange = new DateRange($start, $end);
            }

            if (isset($timeRangeParameter['start']) && isset($timeRangeParameter['end'])) {
                $timeRangeParameter = $this->request->request->get('time_range');
                $timeRange = new TimeRange(
                    new \DateTime('1970-01-01 ' . $timeRangeParameter['start']),
                    new \DateTime('1970-01-01 ' . $timeRangeParameter['end'])
                );
            }
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
     * 2: Voucher code success
     * 1: Success
     * 0: if form is not submitted:
     * -1: if form is not valid
     * -2: Self booking error
     * -3: Voucher error on code
     * -4: Voucher error on booking amount
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST')) {
            if ($result = $this->checkVoucher($form)) {
                return $result;//2, -3 or -4
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
     * Check voucher.
     * todo: decouple voucher
     *
     * @param $form
     *
     * @return bool|int equal to:
     *  2: Voucher code success
     *  -3: Voucher error on code
     *  -4: Voucher error on booking amount
     *
     * @throws \Exception
     */
    private function checkVoucher(Form $form)
    {
        $result = false;

        if ($this->bookingManager->voucherIsEnabled()) {
            $voucherForm = $form->get('voucher');
            //Check only if Ok is clicked
            if ($voucherForm->get('validateVoucher')->isClicked()) {
                /** @var Booking $booking */
                $booking = $form->getData();
                if (!$voucherForm->get('codeVoucher')->isValid()) {
                    if (!$booking->getAmountDiscountVoucher()) {
                        $result = -3;//Code invalid
                    } else {
                        $result = -4;//Booking amount invalid
                    }
                } elseif ($booking->getAmountDiscountVoucher()) {
                    $result = 2;//Success
                }
            }
        }

        return $result;
    }


    /**
     * createFromFormat  MP Card and Pre auth. Save Booking.
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -2: Self booking error
     */
    private function onSuccess(Form $form)
    {
        /** @var Booking $booking */
        $request = $this->request->request;
        $booking = $form->getData();
        $user = $booking->getUser();

        //Login is done in BookingNewType form
        if ($request->get('_username') || $request->get('_password')) {
        } //Register : Authentication and Welcome email after registration
        elseif ($form->has('user') && $form->get('user')->has("email")) {
            $this->registrationHandler->handleRegistration($user);
        }

        if ($booking->getUser() == $booking->getListing()->getUser()) {
            $result = -2;

            return $result;
        }

        return 1;
    }

}
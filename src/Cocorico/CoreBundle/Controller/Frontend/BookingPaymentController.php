<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\Booking;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Booking Payment controller.
 *
 * @Route("/booking/payment")
 */
class BookingPaymentController extends Controller
{
    /**
     * Payment page.
     *
     * @Route("/{booking_id}/new",
     *      name="cocorico_booking_payment_new",
     *      requirements={
     *          "booking_id" = "\d+"
     *      },
     * )
     *
     * @Security("is_granted('create', booking) and not has_role('ROLE_ADMIN') and has_role('ROLE_USER')")
     *
     * @ParamConverter("booking", class="CocoricoCoreBundle:Booking", options={"id" = "booking_id"})
     *
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param Booking $booking
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, Booking $booking)
    {
        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addBookingNewItems($request, $booking);

        return $this->render(
            'CocoricoCoreBundle:Frontend/BookingPayment:new.html.twig',
            array(
                'booking' => $booking,
            )
        );
    }
}

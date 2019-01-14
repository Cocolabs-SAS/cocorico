<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\BookingBankWire;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Booking Bank Wire Dashboard controller.
 *
 * @Route("/offerer/booking-bank-wire")
 */
class BookingBankWireController extends Controller
{

    /**
     * Lists all booking Bank Wire entities.
     *
     * @Route("/{page}", name="cocorico_dashboard_booking_bank_wire_offerer", defaults={"page" = 1})
     * @Method("GET")
     *
     * @param  Request $request
     * @param  int     $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page)
    {
        $bookingBankWireManager = $this->get('cocorico.booking_bank_wire.manager');
        $bookingBankWires = $bookingBankWireManager->findByOfferer(
            $this->getUser()->getId(),
            $page
        );

        return $this->render(
            'CocoricoCoreBundle:Dashboard/BookingBankWire:index.html.twig',
            array(
                'booking_bank_wires' => $bookingBankWires,
                'pagination' => array(
                    'page' => $page,
                    'pages_count' => ceil($bookingBankWires->count() / $bookingBankWireManager->maxPerPage),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                )
            )
        );
    }


    /**
     * Show booking Bank Wire bill.
     *
     * @Route("/{id}/show-bill", name="cocorico_dashboard_booking_bank_wire_show_bill_offerer", requirements={"id" = "\d+"})
     * @Method("GET")
     *
     * @param  int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showBillAction($id)
    {
        $bookingBankWireManager = $this->get('cocorico.booking_bank_wire.manager');
        $bookingBankWire = $bookingBankWireManager->findOneByOfferer(
            $id,
            $this->getUser()->getId(),
            array(BookingBankWire::STATUS_PAYED)
        );

        if (!$bookingBankWire) {
            throw $this->createNotFoundException('Bill not found');
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/BookingBankWire:show_bill.html.twig',
            array(
                'booking_bank_wire' => $bookingBankWire,
            )
        );
    }

}

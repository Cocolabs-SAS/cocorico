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

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Frontend\BookingPriceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingPriceSimulatorController extends Controller
{
    /**
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function priceSimulatorFormAction($listing)
    {
        $bookingPriceHandler = $this->get('cocorico.form.handler.booking_price');
        $booking = $bookingPriceHandler->init($this->getUser(), $listing);

        $form = $this->createPriceSimulatorForm($booking);

        return $this->render(
            '@CocoricoCore/Dashboard/Listing/form_price_simulator.html.twig',
            array(
                'form' => $form->createView(),
                'booking' => $booking
            )
        );
    }

    /**
     * @param Booking $booking
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createPriceSimulatorForm(Booking $booking)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            BookingPriceType::class,
            $booking,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_price_simulator',
                    array('id' => $booking->getListing()->getId())
                ),
            )
        );

        return $form;
    }

    /**
     * Price simulation
     *
     * @Route("/{id}/price_simulator", name="cocorico_dashboard_listing_price_simulator", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing")
     *
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function priceSimulatorAction(Request $request, Listing $listing)
    {
        $bookingPriceHandler = $this->get('cocorico.form.handler.booking_price');
        $booking = $bookingPriceHandler->init($this->getUser(), $listing);

        $form = $this->createPriceSimulatorForm($booking);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();

        if ($request->isXmlHttpRequest()) {
            return $this->render(
                'CocoricoCoreBundle:Dashboard/Listing:form_price_simulator.html.twig',
                array(
                    'booking' => $booking,
                    'form' => $form->createView()
                )
            );
        } else {
            if (!$formIsValid) {
                $this->get('cocorico.helper.global')->addFormErrorMessagesToFlashBag(
                    $form,
                    $this->get('session')->getFlashBag()
                );
            }

            return new RedirectResponse($request->headers->get('referer'));
        }
    }


}

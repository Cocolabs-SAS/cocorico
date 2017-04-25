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

use Cocorico\CoreBundle\Document\ListingAvailability;
use Cocorico\CoreBundle\Entity\Listing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingAvailabilityPriceController extends Controller
{

    /**
     * Edits an existing Listing entity.
     *
     * @Route("/{listing_id}/edit_availabilities_prices",
     *      name="cocorico_dashboard_listing_edit_availabilities_prices",
     *      requirements={"listing_id" = "\d+"}
     * )
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     *
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param         $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAvailabilitiesPricesAction(Request $request, Listing $listing)
    {
        $translator = $this->get('translator');
        $selfUrl = $this->generateUrl(
            'cocorico_dashboard_listing_edit_availabilities_prices',
            array('listing_id' => $listing->getId())
        );

        $listingAvailabilityHandler = $this->get('cocorico.form.handler.listing_availability.price.dashboard');
        $form = $this->createEditAvailabilitiesPricesForm($listing);
        $success = $listingAvailabilityHandler->process($form);

        if ($success == 1) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $translator->trans('listing.edit.success', array(), 'cocorico_listing')
            );

            return $this->redirect($selfUrl);
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:edit_availabilities_prices.html.twig',
            array(
                'listing' => $listing,
                'form_prices' => $form->createView()
            )
        );

    }

    /**
     * Creates a form to edit many prices Documents.
     *
     * @param Listing $listing The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditAvailabilitiesPricesForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availabilities_prices',
            'listing_edit_availabilities_prices',
            $listing,
            array(
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_availabilities_prices',
                    array('listing_id' => $listing->getId())
                ),
                'method' => 'POST',
            )
        );

        return $form;
    }


    /**
     * New ListingAvailability Document.
     *
     * @Route("/{listing_id}/{day}/new_availability_price",
     *      name="cocorico_dashboard_listing_new_availability_price",
     *      requirements={
     *          "listing_id" = "\d+",
     *          "day"= "^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$"
     *      },
     * )
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     *
     * @Method({"GET", "POST"})
     *
     * @param  Request $request
     * @param  Listing $listing
     * @param  string  $day format yyyy-mm-dd
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, Listing $listing, $day)
    {
        $availability = new ListingAvailability();
        $availability->setListingId($listing->getId());
        $availability->setDay(new \DateTime($day));

        $form = $this->createCreateForm($availability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get("cocorico.listing_availability.manager")->saveAvailability(
                $form->getData()
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.availability.new.success', array(), 'cocorico_listing')
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:form_availability_price.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param ListingAvailability $availability
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createCreateForm(ListingAvailability $availability)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availability',
            'listing_edit_availability_price',
            $availability,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_new_availability_price',
                    array(
                        'listing_id' => $availability->getListingId(),
                        'day' => $availability->getDay()->format('Y-m-d')
                    )
                ),
            )
        );

        return $form;
    }


    /**
     * Create Or Update a ListingAvailability Document.
     *
     * @Route("/{listing_id}/{id}/{start_time}/{end_time}/edit_availability_price",
     *      name="cocorico_dashboard_listing_edit_availability_price",
     *      requirements={
     *          "listing_id" = "\d+",
     *          "id" = "^[a-z0-9]+$",
     *          "start_time" = "^([01]?[0-9]|2[0-3]):[0-5][0-9]$",
     *          "end_time" = "^([01]?[0-9]|2[0-3]):[0-5][0-9]$"
     *      },
     * )
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     * @ParamConverter("listing_availability", class="Cocorico\CoreBundle\Document\ListingAvailability")
     *
     * @Method({"GET", "POST"})
     *
     * @param  Request             $request
     * @param  Listing             $listing
     * @param  ListingAvailability $listing_availability
     * @param  string              $start_time
     * @param  string              $end_time
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(
        Request $request,
        Listing $listing,
        ListingAvailability $listing_availability,
        $start_time,
        $end_time
    ) {
        $listingAvailabilityManager = $this->get("cocorico.listing_availability.manager");

        if ($listing->getId() != $listing_availability->getListingId()) {
            throw new AccessDeniedHttpException("Edition impossible");
        }

        //No status edition if already booked
        if ($listingAvailabilityManager->getTimeUnitIsDay() &&
            $listing_availability->getStatus() == ListingAvailability::STATUS_BOOKED
        ) {
            throw $this->createNotFoundException('Edition impossible');
        }

        $form = $this->createEditForm($listing_availability, $start_time, $end_time);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ListingAvailability $listing_availability */
            $listing_availability = $form->getData();
            $listingAvailabilityManager->saveAvailabilityTimes(
                $listing_availability,
                $start_time,
                $end_time,
                "price"
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.availability.edit.success', array(), 'cocorico_listing')
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:form_availability_price.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }


    /**
     * @param ListingAvailability $availability
     * @param  string             $start_time
     * @param  string             $end_time
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createEditForm(ListingAvailability $availability, $start_time, $end_time)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availability',
            'listing_edit_availability_price',
            $availability,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_availability_price',
                    array(
                        'listing_id' => $availability->getListingId(),
                        'id' => $availability->getId(),
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                    )
                ),
            )
        );

        return $form;
    }

}

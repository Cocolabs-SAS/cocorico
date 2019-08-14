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
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditAvailabilitiesStatusType;
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditAvailabilityStatusType;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingAvailabilityStatusController extends Controller
{

    /**
     * Edits listing availabilities status.
     *
     * @Route("/{listing_id}/edit_availabilities_status",
     *      name="cocorico_dashboard_listing_edit_availabilities_status",
     *      requirements={"listing_id" = "\d+"}
     * )
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     *
     * @Method({"GET", "POST"})
     *
     * @param         $listing
     *
     * @return RedirectResponse|Response
     */
    public function editAvailabilitiesStatusAction(Listing $listing)
    {
        $listingAvailabilityHandler = $this->get('cocorico.form.handler.listing_availability.status.dashboard');
        $form = $this->createEditAvailabilitiesStatusForm($listing);
        $success = $listingAvailabilityHandler->processMany($form);

        if ($success == 1) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.edit.success', array(), 'cocorico_listing')
            );

            return $this->redirectToRoute(
                'cocorico_dashboard_listing_edit_availabilities_status',
                array('listing_id' => $listing->getId())
            );
        } elseif ($success == -1) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('form.error', array(), 'cocorico')
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:edit_availabilities_status.html.twig',
            array(
                'listing' => $listing,
                'form' => $form->createView()
            )
        );

    }

    /**
     * Creates a form to edit many ListingAvailability Documents.
     *
     * @param Listing $listing The entity
     *
     * @return Form The form
     */
    private function createEditAvailabilitiesStatusForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availabilities_status',
            ListingEditAvailabilitiesStatusType::class,
            $listing,
            array(
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_availabilities_status',
                    array('listing_id' => $listing->getId())
                ),
                'method' => 'POST',
            )
        );

        return $form;
    }


    /**
     * Update a ListingAvailability Document.
     *
     * @Route("/{listing_id}/{day}/{start_time}/{end_time}/edit_availability_status",
     *      name="cocorico_dashboard_listing_edit_availability_status",
     *      requirements={
     *          "listing_id" = "\d+",
     *          "day"= "^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$",
     *          "start_time" = "^([01]?[0-9]|2[0-3]):[0-5][0-9]$",
     *          "end_time" = "^([01]?[0-9]|2[0-3]):[0-5][0-9]$",
     *      },
     * )
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     *
     * @Method({"GET", "POST"})
     *
     * @param  Listing $listing
     * @param  string  $day
     * @param  string  $start_time
     * @param  string  $end_time
     *
     * @return Response
     */
    public function editAvailabilityStatusAction(
        Listing $listing,
        $day,
        $start_time,
        $end_time
    ) {
        $listingAvailabilityHandler = $this->get('cocorico.form.handler.listing_availability.status.dashboard');
        $form = $this->createEditAvailabilityStatusForm($listing->getId(), $day, $start_time, $end_time);
        $success = $listingAvailabilityHandler->processOne($form, $listing, $day, $start_time, $end_time);

        if ($success == 1) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.availability.edit.success', array(), 'cocorico_listing')
            );
        } elseif ($success == -1) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('form.error', array(), 'cocorico')
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:form_availability_status.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }


    /**
     * @param int     $listingId
     * @param string  $day
     * @param  string $startTime
     * @param  string $endTime
     *
     * @return Form|FormInterface
     */
    private function createEditAvailabilityStatusForm($listingId, $day, $startTime, $endTime)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availability',
            ListingEditAvailabilityStatusType::class,
            null,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_availability_status',
                    array(
                        'listing_id' => $listingId,
                        'day' => $day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    )
                ),
            )
        );

        return $form;
    }


    /**
     * New ListingAvailability Document.
     *
     * @Route("/{listing_id}/{day}/new_availability_status",
     *      name="cocorico_dashboard_listing_new_availability_status",
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
     * @return Response
     */
    public function newAction(Request $request, Listing $listing, $day)
    {
        $availability = new ListingAvailability();
        $availability->setListingId($listing->getId());
        $availability->setDay(new DateTime($day));
        $availability->setPrice($listing->getPrice());

        $form = $this->createCreateForm($availability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get("cocorico.listing_availability.manager")->save($form->getData());

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.availability.new.success', array(), 'cocorico_listing')
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:form_availability_status.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param ListingAvailability $availability
     *
     * @return Form|FormInterface
     */
    private function createCreateForm(ListingAvailability $availability)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availability',
            ListingEditAvailabilityStatusType::class,
            $availability,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_new_availability_status',
                    array(
                        'listing_id' => $availability->getListingId(),
                        'day' => $availability->getDay()->format('Y-m-d')
                    )
                )
            )
        );

        return $form;
    }

}

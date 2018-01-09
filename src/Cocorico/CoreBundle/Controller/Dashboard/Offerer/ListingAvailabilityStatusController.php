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
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditAvailabilityStatusType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingAvailabilityStatusController extends Controller
{

    /**
     * Edits an existing Listing entity.
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
     * @param Request $request
     * @param         $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAvailabilitiesStatusAction(Request $request, Listing $listing)
    {
        $translator = $this->get('translator');
        $selfUrl = $this->generateUrl(
            'cocorico_dashboard_listing_edit_availabilities_status',
            array('listing_id' => $listing->getId())
        );

        $listingAvailabilityHandler = $this->get('cocorico.form.handler.listing_availability.status.dashboard');
        $form = $this->createEditAvailabilitiesStatusForm($listing);
        $success = $listingAvailabilityHandler->process($form);

        if ($success == 1) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $translator->trans('listing.edit.success', array(), 'cocorico_listing')
            );

            return $this->redirect($selfUrl);
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
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditAvailabilitiesStatusForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availabilities_status',
            'listing_edit_availabilities_status',
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, Listing $listing, $day)
    {
        $availability = new ListingAvailability();
        $availability->setListingId($listing->getId());
        $availability->setDay(new \DateTime($day));
        $form = $this->createCreateForm($availability, $listing->getPrice());
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
            'CocoricoCoreBundle:Dashboard/Listing:form_availability_status.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param ListingAvailability $availability
     * @param int                 $defaultPrice
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createCreateForm(ListingAvailability $availability, $defaultPrice)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availability',
            new ListingEditAvailabilityStatusType(),
            $availability,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_new_availability_status',
                    array(
                        'listing_id' => $availability->getListingId(),
                        'day' => $availability->getDay()->format('Y-m-d')
                    )
                ),
                'defaultPrice' => $defaultPrice
            )
        );

        return $form;
    }


    /**
     * Create Or Update a ListingAvailability Document.
     *
     * @Route("/{listing_id}/{id}/{start_time}/{end_time}/edit_availability_status",
     *      name="cocorico_dashboard_listing_edit_availability_status",
     *      requirements={
     *          "listing_id" = "\d+",
     *          "id" = "^[a-z0-9]+$",
     *          "start_time" = "^([01]?[0-9]|2[0-3]):[0-5][0-9]$",
     *          "end_time" = "^([01]?[0-9]|2[0-3]):[0-5][0-9]$",
     *          "day"= "^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$"
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

            //convert object to array
            $listingAvailability = $listingAvailabilityManager->listingAvailabilityToArray($listing_availability);

            $listingAvailabilityManager->saveAvailabilityTimes(
                $listingAvailability,
                $start_time,
                $end_time,
                "status",
                $listing->getPrice()
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.availability.edit.success', array(), 'cocorico_listing')
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
     * @param  string             $start_time
     * @param  string             $end_time
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createEditForm(ListingAvailability $availability, $start_time, $end_time)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_availability',
            new ListingEditAvailabilityStatusType(),
            $availability,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_availability_status',
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

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

use Cocorico\CoreBundle\Entity\Listing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listing Duplicate Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingDuplicateController extends Controller
{
    /**
     * Duplicate Listing.
     *
     * @Route("/{id}/duplicate", name="cocorico_dashboard_listing_duplicate", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing")
     *
     * @Method({"GET"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function duplicateAction(Request $request, Listing $listing)
    {
        //Duplicate listing
        $duplicatedListing = $this->get("cocorico.listing.manager")->duplicate($listing);

        if ($duplicatedListing->getId()) {
            //Duplicate availabilities
            $this->get("cocorico.listing_availability.manager")->duplicate(
                $listing->getId(),
                $duplicatedListing->getId(),
                $this->container->getParameter('cocorico.days_max_edition')
            );

            $url = $this->generateUrl(
                'cocorico_dashboard_listing_edit_presentation',
                array(
                    'id' => $duplicatedListing->getId()
                )
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.duplicate.success', array(), 'cocorico_listing')

            );
        } else {
            $url = $request->headers->get('referer');

            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('listing.duplicate.error', array(), 'cocorico_listing')

            );
        }

        return $this->redirect($url);
    }
}

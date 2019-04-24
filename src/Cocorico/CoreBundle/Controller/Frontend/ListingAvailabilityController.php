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

use Cocorico\CoreBundle\Entity\Listing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listing Availability controller.
 *
 * @Route("/listing_availabilities")
 */
class ListingAvailabilityController extends Controller
{

    /**
     * Lists ListingAvailability Documents
     *
     * @Route("/{listing_id}/{start}/{end}",
     *      name="cocorico_listing_availabilities",
     *      requirements={
     *          "listing_id" = "\d+",
     *          "start"= "^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$",
     *          "end"= "^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$",
     *          "_format"="json"
     *      },
     *      defaults={"_format": "json"}
     * )
     * @Security("is_granted('view', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     *
     * @Method("GET")
     *
     * @param  Request $request
     * @param  Listing $listing
     * @param  string  $start format yyyy-mm-dd
     * @param  string  $end   format yyyy-mm-dd
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, Listing $listing = null, $start, $end)
    {
        $timezone = $this->get('session')->get('timezone');

        $start = new \DateTime($start);
        $start->modify('-1 day');//For timezone purposes
        $end = new \DateTime($end);

        $availabilities = $this->get("cocorico.listing_availability.manager")->getCalendarEvents(
            $listing->getId(),
            $start,
            $end,
            false,
            $timezone
        );

        //Convert and format prices
        $locale = $request->getLocale();
        $coreExtension = $this->get('cocorico.twig.core_extension');
        array_walk(
            $availabilities,
            function (&$el, $key, $locale) use ($coreExtension) {
                $el["title"] = $coreExtension->formatPriceFilter($el["title"], $locale, 0);
            },
            $locale
        );

        return new JsonResponse($availabilities);
    }
}

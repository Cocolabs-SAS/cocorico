<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\Controller;

use Cocorico\CoreBundle\Entity\Listing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listing controller.
 *
 * @Route("/geocoding")
 */
class GeocodingController extends Controller
{
    /**
     * Add new geocoding informations to existing geographical entities
     *
     * @Route("/{slug}/create", name="cocorico_geo_create", requirements={
     *      "slug" = "[a-z0-9-]+$"
     * })
     * @Method("POST")
     * @ParamConverter("listing", class="Cocorico\CoreBundle\Entity\Listing", options={"repository_method" = "findOneBySlug"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return Response
     */
    public function createAction(Request $request, Listing $listing)
    {
        $type = $request->request->get('type');
        $geocoding = $request->request->get('geocoding');

        $this->get('cocorico_geo.geocoding.manager')->createGeocoding($listing, $type, $geocoding);

        return new Response('true');
    }
}

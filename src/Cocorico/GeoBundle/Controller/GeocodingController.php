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

use Cocorico\GeoBundle\Entity\Coordinate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listing controller.
 *
 * @Route("/geocoding")
 */
class GeocodingController extends Controller
{
    /**
     * Create new geocoding entity for a particular coordinate entity
     *
     * @Route("/{id}/create", name="cocorico_geo_create", requirements={
     *      "id" = "\d+"
     * })
     * @Method("POST")
     * @ParamConverter("coordinate", class="Cocorico\GeoBundle\Entity\Coordinate")
     *
     * @param Coordinate $coordinate
     *
     * @return Response
     */
    public function createAction(Coordinate $coordinate)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $type = $request->request->get('type');
        $geocoding = $request->request->get('geocoding');

        $this->get('cocorico_geo.geocoding.manager')->createGeocoding($coordinate, $type, $geocoding);

        return new Response('true');
    }
}

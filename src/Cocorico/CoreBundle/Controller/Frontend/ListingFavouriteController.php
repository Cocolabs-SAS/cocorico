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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ListingFavouriteController extends ListingSearchController
{

    /**
     * Favourites Listings result.
     *
     * @Route("/listing/favourite", name="cocorico_listing_favourite")
     * @Method("GET")
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexFavouriteAction(Request $request)
    {
        $markers = array();
        $resultsIterator = new \ArrayIterator();
        $nbResults = 0;

        $listingSearchRequest = $this->get('cocorico.listing_search_request');
        $form = $this->createSearchForm($listingSearchRequest);

        $form->handleRequest($request);

        // handle the form for pagination
        if ($form->isSubmitted() && $form->isValid()) {
            $listingSearchRequest = $form->getData();
        }

        $favourites = explode(',', $request->cookies->get('favourite'));
        if (count($favourites) > 0) {
            $results = $this->get("cocorico.listing_search.manager")->getListingsByIds(
                $favourites,
                $listingSearchRequest->getPage(),
                $request->getLocale()
            );
            $nbResults = $results->count();
            $resultsIterator = $results->getIterator();
            $markers = $this->getMarkers($request, $results, $resultsIterator);
        }

        return $this->render(
            '@CocoricoCore/Frontend/ListingResult/result.html.twig',
            array(
                'results' => $resultsIterator,
                'nb_results' => $nbResults,
                'markers' => $markers,
                'listing_search_request' => $listingSearchRequest,
                'pagination' => array(
                    'page' => $listingSearchRequest->getPage(),
                    'pages_count' => ceil($nbResults / $listingSearchRequest->getMaxPerPage()),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                )
            )
        );

    }

}

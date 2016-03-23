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

use Cocorico\CoreBundle\Repository\ListingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class HomeController
 *
 */
class HomeController extends Controller
{
    /**
     * @Route("/", name="cocorico_home")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('CocoricoCoreBundle:Frontend\Home:index.html.twig');
    }

    /**
     * @param int $limit
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function highRankListingAction($limit)
    {
        /** @var ListingRepository $listingRepository */
        $listingRepository = $this->getDoctrine()->getRepository('CocoricoCoreBundle:Listing');
        $listings = $listingRepository->findByHighestRanking($limit, $this->get('request')->getLocale());

        return $this->render(
            'CocoricoCoreBundle:Frontend/Home:high_rank.html.twig',
            array(
                'listings' => $listings
            )
        );
    }
}

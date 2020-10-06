<?php

namespace Cocorico\CoreBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends Controller
{
    /**
     * @Route("/stats", name="cocorico_stats")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundationResponse
     */
    public function statsAction(Request $request)
    {
        return $this->render(
            'CocoricoCoreBundle:Frontend\Home:stats.html.twig'
        );
    }
}

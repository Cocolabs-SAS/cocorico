<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BreadcrumbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BreadcrumbsController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $breadcrumbsManager = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbsManager->addItemsFromYAML($request, trim($request->get('_route')));

        return $this->render(
            'CocoricoBreadcrumbBundle:Breadcrumbs:index.html.twig'
        );
    }
}

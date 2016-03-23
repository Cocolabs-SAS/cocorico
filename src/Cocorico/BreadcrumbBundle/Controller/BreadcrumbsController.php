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

class BreadcrumbsController extends Controller
{
    /**
     * @param Request $request
     * @param string  $routeName
     *
     * @return array
     */
    public function breadcrumbAction(Request $request, $routeName)
    {
        $breadcrumbsManager = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbsManager->addBreadcrumbsForRoute($request, trim($routeName));

        return $this->render(
            'CocoricoBreadcrumbBundle:Breadcrumbs:breadcrumbs.html.twig'
        );
    }
}

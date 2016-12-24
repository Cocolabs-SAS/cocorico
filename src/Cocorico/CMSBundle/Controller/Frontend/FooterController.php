<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CMSBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Footer frontend controller.
 *
 * @Route("/footer")
 */
class FooterController extends Controller
{
    /**
     * Display footer links
     *
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $footers = $this->get('cocorico_cms.footer.manager')->findByURL(
            $request->getUri(),
            $request->getLocale()
        );

        return $this->render(
            '@CocoricoCMS/Frontend/Footer/index.html.twig',
            array(
                'footers' => $footers
            )
        );

    }
}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Controller\Frontend;

use Cocorico\CoreBundle\Utils\PHP;
use Cocorico\PageBundle\Repository\PageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page frontend controller.
 *
 * @Route("/page")
 */
class PageController extends Controller
{

    /**
     * show page depending upon the slug available.
     *
     * @Route("/{slug}", name="cocorico_page_show")
     *
     * @Method("GET")
     *
     * @param  Request $request
     * @param  string  $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws NotFoundHttpException
     */
    public function showAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PageRepository $page */
        $page = $em->getRepository('CocoricoPageBundle:Page')->findOneBySlug(
            $slug,
            $request->getLocale()
        );
        if (!$page) {
            throw new NotFoundHttpException(sprintf('%s page not found.', $slug));
        }

        PHP::log($request->getHttpHost());

        return $this->render(
            '@CocoricoPage/Frontend/Page/show.html.twig',
            array(
                'page' => $page
            )
        );

    }
}

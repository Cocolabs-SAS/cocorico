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

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Form\Type\Frontend\DirectoryCategoriesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Directory Dashboard category controller.
 *
 * @Route("/directory")
 */
class DirectoryCategoriesController extends Controller
{
    /**
     * @param  Directory $directory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoriesFormAction($directory)
    {
        $form = $this->createCategoriesForm($directory);

        return $this->render(
            '@CocoricoCore/Frontend/Directory/form_categories.html.twig',
            array(
                'form' => $form->createView(),
                'directory' => $directory
            )
        );
    }

    /**
     * @param Directory $directory
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createCategoriesForm(Directory $directory)
    {
        $form = $this->get('form.factory')->createNamed(
            'directory_categories',
            DirectoryCategoriesType::class,
            $directory,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_directory_new_categories'
                ),
            )
        );

        return $form;
    }

    /**
     * New Directory categories in ajax mode.
     *
     * @Route("/new_categories", name="cocorico_dashboard_directory_new_categories")
     *
     * @Method({"POST", "GET"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newCategoriesAction(Request $request)
    {
        $directory = new Directory();
        $directory = $this->get('cocorico.form.handler.directory')->addCategories($directory);
        $form = $this->createCategoriesForm($directory);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();
//        if ($formIsValid) {
//
//        }

        if ($request->isXmlHttpRequest()) {
            return $this->render(
                'CocoricoCoreBundle:Frontend/Directory:form_categories.html.twig',
                array(
                    'directory' => $directory,
                    'form' => $form->createView()
                )
            );
        } else {
            if (!$formIsValid) {
                $this->get('cocorico.helper.global')->addFormErrorMessagesToFlashBag(
                    $form,
                    $this->get('session')->getFlashBag()
                );
            }

            return new RedirectResponse($request->headers->get('referer'));
        }
    }
}

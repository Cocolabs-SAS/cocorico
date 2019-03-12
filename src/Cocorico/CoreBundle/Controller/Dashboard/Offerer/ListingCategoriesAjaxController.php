<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditCategoriesAjaxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listing Dashboard category controller.
 *
 * @Route("/listing")
 */
class ListingCategoriesAjaxController extends Controller
{
    /**
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoriesAjaxFormAction($listing)
    {
        $form = $this->createCategoriesAjaxForm($listing);

        return $this->render(
            '@CocoricoCore/Dashboard/Listing/form_categories_ajax.html.twig',
            array(
                'form' => $form->createView(),
                'listing' => $listing
            )
        );
    }

    /**
     * @param Listing $listing
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createCategoriesAjaxForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_categories',
            ListingEditCategoriesAjaxType::class,
            $listing,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_categories_ajax',
                    array('id' => $listing->getId())
                ),
            )
        );

        return $form;
    }

    /**
     * Edit Listing categories.
     *
     * @Route("/{id}/edit_categories_ajax", name="cocorico_dashboard_listing_edit_categories_ajax", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing")
     *
     * @Method({"POST", "GET"})
     *
     * @param Request $request
     * @param         $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editCategoriesAjaxAction(Request $request, Listing $listing)
    {
        $form = $this->createCategoriesAjaxForm($listing);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();
        if ($formIsValid) {
            $listing = $this->get("cocorico.listing.manager")->save($listing);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.edit.success', array(), 'cocorico_listing')
            );

            return $this->redirectToRoute(
                'cocorico_dashboard_listing_edit_categories_ajax',
                array('id' => $listing->getId())
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:form_categories_ajax.html.twig',
            array(
                'listing' => $listing,
                'form' => $form->createView()
            )
        );
    }

}

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
class ListingCategoriesController extends Controller
{
    /**
     * @param Listing $listing
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createCategoriesForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_categories',
            'listing_edit_categories',
            $listing,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_categories',
                    array('id' => $listing->getId())
                ),
            )
        );

        return $form;
    }

    /**
     * Edit Listing categories.
     *
     * @Route("/{id}/edit_categories", name="cocorico_dashboard_listing_edit_categories", requirements={"id" = "\d+"})
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
    public function editCategoriesAction(Request $request, Listing $listing)
    {
        $form = $this->createCategoriesForm($listing);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();
        if ($formIsValid) {
            $listing = $this->get("cocorico.listing.manager")->save($listing);

            //Tmp solution to resolve the problem of fields values not removed from Form when category is removed from
            // listing, whereas fields values are removed from database.
            //todo: Avoid this and see why it is not done in form
//            $form = $this->createCategoriesForm($listing);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.edit.success', array(), 'cocorico_listing')
            );

            $selfUrl = $this->generateUrl(
                'cocorico_dashboard_listing_edit_categories',
                array('id' => $listing->getId())
            );

            return $this->redirect($selfUrl);
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:edit_categories.html.twig',
            array(
                'listing' => $listing,
                'form' => $form->createView()
            )
        );
    }

}

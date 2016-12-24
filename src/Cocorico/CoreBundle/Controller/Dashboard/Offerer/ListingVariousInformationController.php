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
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingVariousInformationController extends Controller
{

    /**
     * Edits an existing Listing entity.
     *
     * @Route("/{id}/edit_various_information", name="cocorico_dashboard_listing_edit_various_information", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing")
     *
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param         $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editVariousInformationAction(Request $request, Listing $listing)
    {
        $translator = $this->get('translator');
        $editForm = $this->createEditVariousInformationForm($listing);
        $editForm->handleRequest($request);

        $selfUrl = $this->generateUrl(
            'cocorico_dashboard_listing_edit_various_information',
            array(
                'id' => $listing->getId()
            )
        );
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->get("cocorico.listing.manager")->save($listing);

            $this->get('session')->getFlashBag()->add(
                'success',
                $translator->trans('listing.edit.success', array(), 'cocorico_listing')
            );

            return $this->redirect($selfUrl);
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Listing:edit_various_information.html.twig',
            array(
                'listing' => $listing,
                'form' => $editForm->createView()
            )
        );

    }

    /**
     * Creates a form to edit a Listing entity.
     *
     * @param Listing $listing The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditVariousInformationForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing',
            'listing_edit_various_information',
            $listing,
            array(
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_various_information',
                    array('id' => $listing->getId())
                ),
                'method' => 'POST',
            )
        );

        return $form;
    }
}

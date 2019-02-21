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
use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditDiscountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listing Dashboard controller.
 *
 * @Route("/listing")
 */
class ListingDiscountController extends Controller
{
    /**
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function discountFormAction($listing)
    {
        $form = $this->createDiscountForm($listing);

        return $this->render(
            '@CocoricoCore/Dashboard/Listing/form_discount.html.twig',
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
    private function createDiscountForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing_discount',
            ListingEditDiscountType::class,
            $listing,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_discount',
                    array('id' => $listing->getId())
                ),
            )
        );

        return $form;
    }

    /**
     * Edit Listing duration.
     *
     * @Route("/{id}/edit_discount", name="cocorico_dashboard_listing_edit_discount", requirements={"id" = "\d+"})
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
    public function editDiscountAction(Request $request, Listing $listing)
    {
        $form = $this->createDiscountForm($listing);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();
        if ($formIsValid) {
            $this->get("cocorico.listing.manager")->save($listing);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.edit_discount.success', array(), 'cocorico_listing')
            );

            return $this->redirectToRoute(
                'cocorico_dashboard_listing_edit_discount',
                array('id' => $listing->getId())
            );
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render(
                'CocoricoCoreBundle:Dashboard/Listing:form_discount.html.twig',
                array(
                    'listing' => $listing,
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

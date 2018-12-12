<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\ListingDepositBundle\Form\Type\Dashboard\ListingEditDepositType;
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
class ListingDepositController extends Controller
{
    /**
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function depositFormAction($listing)
    {
        $form = $this->createDepositForm($listing);

        return $this->render(
            '@CocoricoListingDeposit/Dashboard/Listing/form_deposit.html.twig',
            array(
                'form' => $form->createView(),
                'listing' => $listing,
            )
        );
    }

    /**
     * @param Listing $listing
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createDepositForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing',
            new ListingEditDepositType(),
            $listing,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_listing_edit_deposit',
                    array('id' => $listing->getId())
                ),
            )
        );

        return $form;
    }

    /**
     * Edit Listing deposit.
     *
     * @Route("/{id}/edit_deposit", name="cocorico_dashboard_listing_edit_deposit", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', listing)")
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing")
     *
     * @Method({"POST"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editDepositAction(Request $request, Listing $listing)
    {
        $form = $this->createDepositForm($listing);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();
        if ($formIsValid) {
            $listing = $this->get("cocorico.listing.manager")->save($listing);
            $this->addFormSuccessMessagesToFlashBag('deposit');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render(
                '@CocoricoListingDeposit/Dashboard/Listing/form_deposit.html.twig',
                array(
                    'form' => $form->createView(),
                    'listing' => $listing,
                )
            );
        } else {
            if (!$formIsValid) {
                $this->addFormErrorMessagesToFlashBag($form);
            }

            return new RedirectResponse($request->headers->get('referer'));
        }
    }

    /**
     * Form Error
     *
     * @param $form
     */
    private function addFormErrorMessagesToFlashBag($form)
    {
        $this->get('cocorico.helper.global')->addFormErrorMessagesToFlashBag(
            $form,
            $this->get('session')->getFlashBag()
        );
    }

    /**
     * Form Success
     *
     * @param $type
     */
    private function addFormSuccessMessagesToFlashBag($type)
    {
        $session = $this->get('session');

        if ($type == 'deposit') {
            $session->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.edit_deposit.success', array(), 'cocorico_listing_deposit')
            );
        }

    }
}

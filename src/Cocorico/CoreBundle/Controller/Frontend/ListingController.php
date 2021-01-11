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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Frontend\ListingNewType;
use Cocorico\CoreBundle\Form\Type\Frontend\ListingNewCharacteristicType;
#use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditCharacteristicType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listing controller.
 *
 * @Route("/listing")
 */
class ListingController extends Controller
{
    /**
     * Creates a new Listing entity.
     *
     * @Route("/new", name="cocorico_listing_new")
     *
     * @Security("not has_role('ROLE_ADMIN') and has_role('ROLE_USER')")
     *
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $formHandler = $this->get('cocorico.form.handler.listing');


        $listing = $formHandler->init();
        $form = $this->createCreateForm($listing);
        $success = $formHandler->process($form);


        # $editForm = $this->createEditCharacteristicForm($listing);
        # $editForm->handleRequest($request);

        # if ($editForm->isSubmitted() && $editForm->isValid()) {
        #     $this->get("cocorico.listing.manager")->save($listing);

        #     $this->get('session')->getFlashBag()->add(
        #         'success',
        #         $translator->trans('listing.edit.success', array(), 'cocorico_listing')
        #     );
        # }

        if ($success) {
            $url = $this->generateUrl(
                'cocorico_dashboard_listing_edit_presentation',
                array('id' => $listing->getId())
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('listing.new.success', array(), 'cocorico_listing')
            );

            return $this->redirect($url);
        }

        return $this->render(
            'CocoricoCoreBundle:Frontend/Listing:new.html.twig',
            array(
                'listing' => $listing,
                'form' => $form->createView(),
                # 'editForm' => $editForm->createView(),
            )
        );
    }

    /**
     * Creates a form to create a Listing entity.
     *
     * @param Listing $listing The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing',
            ListingNewType::class,
            $listing,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_listing_new'),
            )
        );

        return $form;
    }

    /**
     * Creates a form to edit a Listing entity.
     *
     * @param Listing $listing The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditCharacteristicForm(Listing $listing)
    {
        $form = $this->get('form.factory')->createNamed(
            'listingChar',
            ListingNewCharacteristicType::class,
            $listing,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_listing_new'),
            )
        );
        return $form;
        // return $this->createForm(
        //     #ListingEditCharacteristicType::class,
        //     ListingNewCharacteristicType::class,
        //     $listing,
        //     array(
        //         'method' => 'POST',
        //         'action' => $this->generateUrl('cocorico_listing_new'),
        //     )
        // );

    }

    /**
     * Finds and displays a Listing entity.
     *
     * @Route("/{slug}/show", name="cocorico_listing_show", requirements={
     *      "slug" = "[a-z0-9-]+$"
     * })
     * @Method("GET")
     * @Security("is_granted('view', listing)")
     * @ParamConverter("listing", class="Cocorico\CoreBundle\Entity\Listing", options={"repository_method" = "findOneBySlug"})
     *
     * @param Request $request
     * @param Listing $listing
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, Listing $listing = null)
    {
        if ($redirect = $this->handleSlugChange($listing, $request->get('slug'))) {
            return $redirect;
        }
        $reviews = $this->get('cocorico.review.manager')->getListingReviews($listing);

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addListingShowItems($request, $listing);

        switch ($listing->getPolRange())
            {
            case 3 :
                $listing_km_range = 500;
                $listing_zoom = 6;
                break;
            case 2 :
                $listing_km_range = 200;
                $listing_zoom = 7;
                break;
            case 1 :
                $listing_km_range = 50;
                $listing_zoom = 8;
                break;
            default:
                $listing_km_range = $listing->getRange();
                $listing_zoom = 11;
            }

        return $this->render(
            'CocoricoCoreBundle:Frontend/Listing:show.html.twig',
            array(
                'listing' => $listing,
                'reviews' => $reviews,
                'listing_km_range' => $listing_km_range,
                'listing_zoom' => $listing_zoom,
            )
        );
    }

    /**
     * Handle listing slug change 301 redirection
     *
     * @param Listing $listing
     * @param         $slug
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function handleSlugChange(Listing $listing, $slug)
    {
        if ($slug != $listing->getSlug()) {
            return $this->redirect(
                $this->generateUrl('cocorico_listing_show', array('slug' => $listing->getSlug())),
                301
            );
        }

        return false;
    }
}

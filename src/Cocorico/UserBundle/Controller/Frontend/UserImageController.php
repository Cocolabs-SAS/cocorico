<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Controller\Frontend;

use Cocorico\UserBundle\Entity\userImage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * UserImage controller.
 *
 * @Route("/user-image")
 */
class UserImageController extends Controller
{

    /**
     * Lists all UserImage entities.
     *
     * @Route("/", name="cocorico_user_image")
     * @Method("GET")
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $images = $em->getRepository('CocoricoUserBundle:ListingImage')->findAll();

        return array(
            'images' => $images,
        );
    }

    /**
     * Deletes a ListingImage entity.
     *
     * @Route("/{id}/delete", name="cocorico_listing_image_delete", requirements={"id" = "\d+"})
     *
     * @Security("is_granted('delete', listingImage)")
     * @ParamConverter("listingImage", class="CocoricoCoreBundle:ListingImage")
     * @Method("DELETE")
     *
     * @param Request   $request
     * @param UserImage $userImage
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, UserImage $userImage)
    {
//        $form = $this->createDeleteForm($id);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $entity = $em->getRepository('CocoricoCoreBundle:ListingImage')->find($id);
//
//            if (!$entity) {
//                throw $this->createNotFoundException('Unable to find ListingImage entity.');
//            }
//
//            $em->remove($entity);
//            $em->flush();
//        }
        return $this->redirect($this->generateUrl('cocorico_user_image'));
    }
}

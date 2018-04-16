<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Controller\Dashboard;

use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ProfileController
 *
 * @Route("/user")
 */
class ProfileAboutMeController extends Controller
{

    /**
     * Edit user profile
     *
     * @Route("/edit-about-me", name="cocorico_user_dashboard_profile_edit_about_me")
     * @Method({"GET", "POST"})
     *
     * @param $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ediAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createAboutMeForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get("cocorico_user.user_manager")->updateUser($user);
            $this->container->get('session')->getFlashBag()->add(
                'success',
                $this->container->get('translator')->trans('user.edit.about_me.success', array(), 'cocorico_user')
            );

            $url = $this->generateUrl('cocorico_user_dashboard_profile_edit_about_me');

            return new RedirectResponse($url);
        }

        return $this->container->get('templating')->renderResponse(
            'CocoricoUserBundle:Dashboard/Profile:edit_about_me.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user
            )
        );

    }


    /**
     * Creates a form to edit a user entity.
     *
     * @param mixed $user
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createAboutMeForm($user)
    {
        $form = $this->get('form.factory')->createNamed(
            'user',
            'user_profile_about_me',
            $user,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_dashboard_profile_edit_about_me'),
            )
        );

        return $form;
    }
}

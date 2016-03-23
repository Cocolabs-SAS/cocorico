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

use Cocorico\UserBundle\Entity\UserAddress;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Cocorico\UserBundle\Form\Type\ProfileContactFormType;
use Cocorico\UserBundle\Form\Type\ProfilePaymentFormType;
use Cocorico\UserBundle\Form\Type\ProfileSwitchFormType;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ProfileController
 *
 * @Route("/user")
 */
class ProfileController extends Controller
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
    public function editAboutMeAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createEditAboutMeForm($user);
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
    private function createEditAboutMeForm($user)
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


    /**
     * Edit user profile
     *
     * @Route("/edit-payment", name="cocorico_user_dashboard_profile_edit_payment")
     * @Method({"GET", "POST"})
     *
     * @param $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editPaymentAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createEditPaymentForm($user);
        $success = $this->get('cocorico_user.form.handler.edit_payment')->process($form);

        $session = $this->container->get('session');
        $translator = $this->container->get('translator');

        if ($success > 0) {
            $session->getFlashBag()->add(
                'success',
                $translator->trans('user.edit.payment.success', array(), 'cocorico_user')
            );

            return $this->redirect(
                $this->generateUrl(
                    'cocorico_user_dashboard_profile_edit_payment'
                )
            );
        } elseif ($success < 0) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('user.edit.payment.error', array(), 'cocorico_user')
            );
        }

        return $this->render(
            'CocoricoUserBundle:Dashboard/Profile:edit_payment.html.twig',
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
    private function createEditPaymentForm($user)
    {
        $form = $this->get('form.factory')->createNamed(
            'user',
            new ProfilePaymentFormType(),
            $user,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_dashboard_profile_edit_payment'),
            )
        );

        return $form;
    }


    /**
     * Edit user profile
     *
     * @Route("/edit-contact", name="cocorico_user_dashboard_profile_edit_contact")
     * @Method({"GET", "POST"})
     *
     * @param $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editContactAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createEditContactForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Eventually change user profile data before update it
            $event = new UserEvent($user);
            $this->get('event_dispatcher')->dispatch(UserEvents::USER_PROFILE_UPDATE, $event);
            $user = $event->getUser();

            $this->get("cocorico_user.user_manager")->updateUser($user);

            $this->container->get('session')->getFlashBag()->add(
                'success',
                $this->container->get('translator')->trans('user.edit.contact.success', array(), 'cocorico_user')
            );

            return $this->redirect(
                $this->generateUrl(
                    'cocorico_user_dashboard_profile_edit_contact'
                )
            );
        }

        return $this->render(
            'CocoricoUserBundle:Dashboard/Profile:edit_contact.html.twig',
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
    private function createEditContactForm($user)
    {
        $addresses = $user->getAddresses();
        if (count($addresses) == 0) {
            $user->addAddress(new UserAddress());
        }
        $form = $this->get('form.factory')->createNamed(
            'user',
            new ProfileContactFormType(),
            $user,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_dashboard_profile_edit_contact'),
            )
        );


        return $form;
    }

    /**
     * Switch profile
     *
     * @Route("/profile-switch", name="cocorico_user_dashboard_profile_switch")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profileSwitchAction(Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createProfileSwitchForm($request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $session->set('profile', $data['profile']);

            $response = $this->redirect($this->generateUrl('cocorico_dashboard_message'));

            $response->headers->setCookie(new Cookie('userType', $data['profile'], 0, '/', null, false, false));

            return $response;
        }

        $type = $request->getSession()->get('profile', 'asker');

        $em = $this->container->get('doctrine')->getManager();
        $nbMessages = $em->getRepository('CocoricoMessageBundle:Message')->getNbUnreadMessage($user, $type);

        return $this->render(
            'CocoricoUserBundle:Dashboard/Profile:profile_switch.html.twig',
            array(
                'form' => $form->createView(),
                'nbMessages' => $nbMessages,
                'type' => $type,
            )
        );
    }

    /**
     * Creates a form to switch a user profile.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createProfileSwitchForm(Request $request)
    {
        $session = $request->getSession();
        if ($session->has('profile')) {
            $selectedProfile = $session->get('profile');
        } else {
            $user = $this->getUser();
            $selectedProfile = ($user && $user->getListings()->count()) ? 'offerer' : 'asker';
            $session->set('profile', $selectedProfile);
        }

        $form = $this->get('form.factory')->createNamed(
            'profileSwitch',
            new ProfileSwitchFormType(),
            array('profile' => $selectedProfile),
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_dashboard_profile_switch'),
                'attr' => array('class' => 'form-switchers')
            )
        );

        return $form;
    }

}

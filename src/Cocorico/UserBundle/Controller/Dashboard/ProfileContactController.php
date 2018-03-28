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
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ProfileController
 *
 * @Route("/user")
 */
class ProfileContactController extends Controller
{
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
}

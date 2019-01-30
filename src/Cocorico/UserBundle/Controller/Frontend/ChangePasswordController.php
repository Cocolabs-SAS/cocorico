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

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ChangePasswordController
 *
 */
class ChangePasswordController extends Controller
{

    /**
     * Change password
     *
     * @Route("/change-password", name="cocorico_user_change_password")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

            $userManager->updateUser($user);

//            $this->get('session')->getFlashBag()->add(
//                'success',
//                $this->get('translator')->trans('user.change_password.success', array(), 'cocorico_user')
//            );
//            $url = $this->get('router')->generate('cocorico_user_change_password');

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('cocorico_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(
                FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            return $response;
        }

        return $this->render(
            '@CocoricoUser/Frontend/ChangePassword/changePassword.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }


//    /**
//     * Change password
//     *
//     * @Route("/change-password", name="cocorico_user_change_password")
//     * @Method({"GET", "POST"})
//     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     * @throws AccessDeniedException
//     */
//    public function changePasswordAction()
//    {
//        $user = $this->container->get('security.token_storage')->getToken()->getUser();
//        if (!is_object($user) || !$user instanceof UserInterface) {
//            throw new AccessDeniedException('This user does not have access to this section.');
//        }
//
//        $form = $this->container->get('fos_user.change_password.form');
//        $formHandler = $this->container->get('fos_user.change_password.form.handler');
//
//        $process = $formHandler->process($user);
//        if ($process) {
//            $this->container->get('session')->getFlashBag()->add(
//                'success',
//                $this->container->get('translator')->trans('user.change_password.success', array(), 'cocorico_user')
//            );
//
//            $url = $this->container->get('router')->generate('cocorico_user_change_password');
//
//            return new RedirectResponse($url);
//        }
//
//        return $this->container->get('templating')->renderResponse(
//            '@CocoricoUser/Frontend/ChangePassword/changePassword.html.twig',
//            array('form' => $form->createView())
//        );
//    }


}

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

use Cocorico\UserBundle\Entity\User;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class RegistrationController
 *
 */
class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="cocorico_user_register")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
//                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $url = $this->generateUrl('cocorico_user_register_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(
                    FOSUserEvents::REGISTRATION_COMPLETED,
                    new FilterUserResponseEvent($user, $request, $response)
                );

                return $response;
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render(
            'CocoricoUserBundle:Frontend/Registration:register.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }


//    /**
//     * Register user
//     * @param Request $request
//     * @Route("/register", name="cocorico_user_register")
//     *
//     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     */
//    public function registerAction(Request $request)
//    {
//        $session = $this->container->get('session');
//        $router = $this->container->get('router');
//        $request = $this->container->get('request');
//
//        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            if (!$session->has('profile')) {
//                $session->set('profile', 'asker');
//            }
//            $url = $router->generate('cocorico_home');
//
//            return new RedirectResponse($url);
//        } else {
//            $form = $this->container->get('fos_user.registration.form');
//            /** @var RegistrationFormHandler $formHandler */
//            $formHandler = $this->container->get('fos_user.registration.form.handler');
//            $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');
//
//            $process = $formHandler->process($confirmationEnabled);
//            if ($process) {
//                $user = $form->getData();
//
//                $session->getFlashBag()->add(
//                    'success',
//                    $this->container->get('translator')->trans('user.register.success', array(), 'cocorico_user')
//                );
//
//                if ($confirmationEnabled) {
//                    $session->set('cocorico_user_send_confirmation_email/email', $user->getEmail());
//                    $url = $router->generate('cocorico_user_registration_check_email');
//                } else {
//                    $url = $request->get('redirect_to') ? $request->get('redirect_to') :
//                        $this->container->get('router')->generate('cocorico_user_register_confirmed');
//                }
//
//                return new RedirectResponse($url);
//            }
//
//            return $this->container->get('templating')->renderResponse(
//                'CocoricoUserBundle:Frontend/Registration:register.html.twig',
//                array(
//                    'form' => $form->createView(),
//                )
//            );
//        }
//
//    }



    /**
     * Tell the user to check their email provider.
     *
     * @Route("/check-email", name="cocorico_user_registration_check_email")
     * @Method("GET")
     *
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     */
    public function checkEmailAction()
    {
        $email = $this->get('session')->get('cocorico_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->get('router')->generate('cocorico_user_register'));
        }

        $this->get('session')->remove('cocorico_user_send_confirmation_email/email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render(
            'CocoricoUserBundle:Frontend/Registration:checkEmail.html.twig',
            array(
                'user' => $user,
            )
        );
    }


//    /**
//     *  Tell the user to check his email provider
//     *
//     * @Route("/check-email", name="cocorico_user_registration_check_email")
//     * @Method("GET")
//     *
//     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     */
//    public function checkEmailAction()
//    {
//        $email = $this->container->get('session')->get('cocorico_user_send_confirmation_email/email');
//        $this->container->get('session')->remove('cocorico_user_send_confirmation_email/email');
//        $user = $this->container->get('cocorico_user.user_manager')->findUserByEmail($email);
//
//        if (null === $user) {
//            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
//        }
//
//        return $this->container->get('templating')->renderResponse(
//            'CocoricoUserBundle:Frontend/Registration:checkEmail.html.twig',
//            array(
//                'user' => $user,
//            )
//        );
//    }


    /**
     * Receive the confirmation token from user email provider, login the user.
     *
     * @Route("/register-confirmation/{token}", name="cocorico_user_register_confirmation")
     * @Method("GET")
     *
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        /** @var User $user */
        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setEmailVerified(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('cocorico_user_register_confirmed');
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_CONFIRMED,
            new FilterUserResponseEvent($user, $request, $response)
        );

        return $response;
    }

//    /**
//     * Receive the confirmation token from user email provider, login the user
//     *
//     * @Route("/register-confirmation/{token}", name="cocorico_user_register_confirmation")
//     * @Method("GET")
//     *
//     * @param string $token
//     *
//     *
//     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     * @throws NotFoundHttpException
//     */
//    public function confirmAction($token)
//    {
//        /** @var User $user */
//        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);
//
//        if (null === $user) {
//            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
//        }
//
//        $user->setConfirmationToken(null);
//        $user->setLastLogin(new \DateTime());
//        $user->setEmailVerified(true);
//
//        /** @var RegistrationFormHandler $formHandler */
//        $formHandler = $this->container->get('fos_user.registration.form.handler');
//        $formHandler->handleRegistration($user);
//
//        $response = new RedirectResponse($this->container->get('router')->generate('cocorico_user_register_confirmed'));
//
//        return $response;
//    }


    /**
     * Tell the user his account is now confirmed.
     *
     * @Route("/register-confirmed", name="cocorico_user_register_confirmed")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render(
            'CocoricoUserBundle:Frontend/Registration:confirmed.html.twig',
            array(
                'user' => $user,
                'targetUrl' => $this->getTargetUrlFromSession(),
            )
        );
    }

//    /**
//     * Tell the user his account is now confirmed
//     *
//     * @Route("/register-confirmed", name="cocorico_user_register_confirmed")
//     *
//     * @return \Symfony\Component\HttpFoundation\Response
//     * @throws AccessDeniedException
//     */
//    public function confirmedAction()
//    {
//        $user = $this->container->get('security.token_storage')->getToken()->getUser();
//        if (!is_object($user) || !$user instanceof UserInterface) {
//            throw new AccessDeniedException('This user does not have access to this section.');
//        }
//
//        return $this->container->get('templating')->renderResponse(
//            'CocoricoUserBundle:Frontend/Registration:confirmed.html.twig',
//            array(
//                'user' => $user,
//            )
//        );
//    }

    /**
     * @return mixed
     */
    private function getTargetUrlFromSession()
    {
        $key = sprintf('_security.%s.target_path', $this->get('security.token_storage')->getToken()->getProviderKey());

        if ($this->get('session')->has($key)) {
            return $this->get('session')->get($key);
        }
    }
}

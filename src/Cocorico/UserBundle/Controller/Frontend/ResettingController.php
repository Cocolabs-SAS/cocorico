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
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller managing the resetting of the password.
 *
 */
class ResettingController extends Controller
{
//    const SESSION_EMAIL = 'cocorico_user_send_resetting_email/email';

    /**
     * Request reset user password
     *
     * @Route("/password-resetting-request", name="cocorico_user_resetting_request")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestAction()
    {
        return $this->render('CocoricoUserBundle:Frontend/Resetting:request.html.twig');
    }

    /**
     * Request reset user password: submit form and send email.
     *
     * @Route("/password-resetting-send-email", name="cocorico_user_resetting_send_email")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $ttl = $this->container->getParameter('fos_user.resetting.retry_ttl');

        if (null !== $user && !$user->isPasswordRequestNonExpired($ttl)) {
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                /** @var $tokenGenerator TokenGeneratorInterface */
                $tokenGenerator = $this->get('fos_user.util.token_generator');
                $user->setConfirmationToken($tokenGenerator->generateToken());
            }

            /* Dispatch confirm event */
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

//            $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
            $this->get('cocorico_user.mailer.twig_swift')->sendResettingEmailMessageToUser($user);

            $user->setPasswordRequestedAt(new \DateTime());
            $this->get('fos_user.user_manager')->updateUser($user);

            /* Dispatch completed event */
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }

        return new RedirectResponse(
            $this->generateUrl('cocorico_user_resetting_check_email', array('username' => $username))
        );
    }

//    /**
//     * Request reset user password: submit form and send email
//     *
//     * @Route("/password-resetting-send-email", name="cocorico_user_resetting_send_email")
//     * @Method("POST")
//     *
//     *
//     * @return RedirectResponse
//     */
//    public function sendEmailAction()
//    {
//        $username = $this->container->get('request')->request->get('username');
//
//        /** @var $user UserInterface */
//        $user = $this->container->get('cocorico_user.user_manager')->findUserByUsernameOrEmail($username);
//
//        if (null === $user) {
//            return $this->container->get('templating')->renderResponse(
//                'CocoricoUserBundle:Frontend/Resetting:request.html.twig',
//                array('invalid_username' => $username)
//            );
//        }
//
//        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
//            return $this->container->get('templating')->renderResponse(
//                'CocoricoUserBundle:Frontend/Resetting:passwordAlreadyRequested.html.twig'
//            );
//        }
//
//        if (null === $user->getConfirmationToken()) {
//            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
//            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
//            $user->setConfirmationToken($tokenGenerator->generateToken());
//        }
//
//        $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
//        $this->container->get('cocorico_user.mailer.twig_swift')->sendResettingEmailMessageToUser($user);
//        $user->setPasswordRequestedAt(new \DateTime());
//        $this->container->get('cocorico_user.user_manager')->updateUser($user);
//
//        return new RedirectResponse(
//            $this->container->get('router')->generate('cocorico_user_resetting_check_email')
//        );
//    }


    /**
     * Tell the user to check his email provider.
     *
     * @Route("/password-resetting-check-email", name="cocorico_user_resetting_check_email")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkEmailAction(Request $request)
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('cocorico_user_resetting_request'));
        }

        return $this->render(
            'CocoricoUserBundle:Frontend/Resetting:checkEmail.html.twig',
            array(
                'tokenLifetime' => ceil($this->container->getParameter('fos_user.resetting.retry_ttl') / 3600),
            )
        );
    }


//    /**
//     * Tell the user to check his email provider
//     *
//     * @Route("/password-resetting-check-email", name="cocorico_user_resetting_check_email")
//     *
//     * @return RedirectResponse
//     */
//    public function checkEmailAction()
//    {
//        $session = $this->container->get('session');
//        $email = $session->get(static::SESSION_EMAIL);
//        $session->remove(static::SESSION_EMAIL);
//
//        if (empty($email)) {
//            // the user does not come from the sendEmail action
//            return new RedirectResponse(
//                $this->container->get('router')->generate('cocorico_user_resetting_request')
//            );
//        }
//
//        return $this->container->get('templating')->renderResponse(
//            'CocoricoUserBundle:Frontend/Resetting:checkEmail.html.twig',
//            array(
//                'email' => $email,
//            )
//        );
//    }

    /**
     * Reset user password.
     *
     * @Route("/password-resetting-reset/{token}", name="cocorico_user_resetting_reset")
     *
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function resetAction(Request $request, $token)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('cocorico_user_profile_show', array('id' => $user->getId()));
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(
                FOSUserEvents::RESETTING_RESET_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            return $response;
        }

        return $this->render(
            'CocoricoUserBundle:Frontend/Resetting:reset.html.twig',
            array(
                'token' => $token,
                'form' => $form->createView(),
            )
        );
    }


//    /**
//     * Reset user password
//     *
//     * @Route("/password-resetting-reset/{token}", name="cocorico_user_resetting_reset")
//     *
//     * @param string $token
//     *
//     * @return RedirectResponse|Response
//     */
//    public function resetAction($token)
//    {
//        $user = $this->container->get('cocorico_user.user_manager')->findUserByConfirmationToken($token);
//
//        if (null === $user) {
//            throw new NotFoundHttpException(
//                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
//            );
//        }
//
//        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
//            return new RedirectResponse($this->container->get('router')->generate('cocorico_user_resetting_request'));
//        }
//
//        $form = $this->container->get('fos_user.resetting.form');
//        $formHandler = $this->container->get('fos_user.resetting.form.handler');
//        $process = $formHandler->process($user);
//
//        if ($process) {
//            $this->container->get('session')->getFlashBag()->add(
//                'success',
//                $this->container->get('translator')->trans('user.resetting.success', array(), 'cocorico_user')
//            );
//            $url = $this->container->get('router')->generate(
//                'cocorico_user_profile_show',
//                array('id' => $user->getId())
//            );
//            $response = new RedirectResponse($url);
//            $this->authenticateUser($user, $response);
//
//            return $response;
//        }
//
//        return $this->container->get('templating')->renderResponse(
//            'CocoricoUserBundle:Frontend/Resetting:reset.html.twig',
//            array(
//                'token' => $token,
//                'form' => $form->createView(),
//            )
//        );
//    }

//    /**
//     * Authenticate a user with Symfony Security
//     *
//     * @param \FOS\UserBundle\Model\UserInterface        $user
//     * @param \Symfony\Component\HttpFoundation\Response $response
//     */
//    protected function authenticateUser(UserInterface $user, Response $response)
//    {
//        try {
//            $this->container->get('fos_user.security.login_manager')->loginUser(
//                $this->container->getParameter('fos_user.firewall_name'),
//                $user,
//                $response
//            );
//        } catch (AccountStatusException $ex) {
//            // We simply do not authenticate users which do not pass the user
//            // checker (not enabled, expired, etc.).
//        }
//    }

}

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
use Cocorico\UserBundle\Form\Handler\RegistrationFormHandler;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class RegistrationController
 *
 */
class RegistrationController extends ContainerAware
{

    /**
     * Register user
     *
     * @Route("/register", name="cocorico_user_register")
     *
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction()
    {
        $session = $this->container->get('session');
        $router = $this->container->get('router');
        $request = $this->container->get('request');

        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (!$session->has('profile')) {
                $session->set('profile', 'asker');
            }
            $url = $router->generate('cocorico_home');

            return new RedirectResponse($url);
        } else {
            $form = $this->container->get('fos_user.registration.form');
            /** @var RegistrationFormHandler $formHandler */
            $formHandler = $this->container->get('fos_user.registration.form.handler');
            $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

            $process = $formHandler->process($confirmationEnabled);
            if ($process) {
                $user = $form->getData();

                $session->getFlashBag()->add(
                    'success',
                    $this->container->get('translator')->trans('user.register.success', array(), 'cocorico_user')
                );

                if ($confirmationEnabled) {
                    $session->set('cocorico_user_send_confirmation_email/email', $user->getEmail());
                    $url = $router->generate('cocorico_user_registration_check_email');
                } else {
                    $url = $request->get('redirect_to') ? $request->get('redirect_to') :
                        $this->container->get('router')->generate('cocorico_user_register_confirmed');
                }

                return new RedirectResponse($url);
            }

            return $this->container->get('templating')->renderResponse(
                'CocoricoUserBundle:Frontend/Registration:register.html.twig',
                array(
                    'form' => $form->createView(),
                )
            );
        }

    }


    /**
     *  Tell the user to check his email provider
     *
     * @Route("/check-email", name="cocorico_user_registration_check_email")
     * @Method("GET")
     *
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('cocorico_user_send_confirmation_email/email');
        $this->container->get('session')->remove('cocorico_user_send_confirmation_email/email');
        $user = $this->container->get('cocorico_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->container->get('templating')->renderResponse(
            'CocoricoUserBundle:Frontend/Registration:checkEmail.html.twig',
            array(
                'user' => $user,
            )
        );
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     *
     * @Route("/register-confirmation/{token}", name="cocorico_user_register_confirmation")
     * @Method("GET")
     *
     * @param string $token
     *
     *
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     */
    public function confirmAction($token)
    {
        /** @var User $user */
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setLastLogin(new \DateTime());
        $user->setEmailVerified(true);

        /** @var RegistrationFormHandler $formHandler */
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $formHandler->handleRegistration($user);

        $response = new RedirectResponse($this->container->get('router')->generate('cocorico_user_register_confirmed'));

        return $response;
    }


    /**
     * Tell the user his account is now confirmed
     *
     * @Route("/register-confirmed", name="cocorico_user_register_confirmed")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->container->get('templating')->renderResponse(
            'CocoricoUserBundle:Frontend/Registration:confirmed.html.twig',
            array(
                'user' => $user,
            )
        );
    }
}

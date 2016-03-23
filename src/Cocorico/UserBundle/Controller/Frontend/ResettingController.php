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

use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

/**
 * Controller managing the resetting of the password
 *
 */
class ResettingController extends ContainerAware
{
    const SESSION_EMAIL = 'cocorico_user_send_resetting_email/email';

    /**
     * Request reset user password
     *
     * @Route("/password-resetting-request", name="cocorico_user_resetting_request")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestAction()
    {
        return $this->container->get('templating')->renderResponse(
            'CocoricoUserBundle:Frontend/Resetting:request.html.twig'
        );
    }

    /**
     * Request reset user password: submit form and send email
     *
     * @Route("/password-resetting-send-email", name="cocorico_user_resetting_send_email")
     * @Method("POST")
     *
     *
     * @return RedirectResponse
     */
    public function sendEmailAction()
    {
        $username = $this->container->get('request')->request->get('username');

        /** @var $user UserInterface */
        $user = $this->container->get('cocorico_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse(
                'CocoricoUserBundle:Frontend/Resetting:request.html.twig',
                array('invalid_username' => $username)
            );
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->container->get('templating')->renderResponse(
                'CocoricoUserBundle:Frontend/Resetting:passwordAlreadyRequested.html.twig'
            );
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
        $this->container->get('cocorico_user.mailer.twig_swift')->sendResettingEmailMessageToUser($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('cocorico_user.user_manager')->updateUser($user);

        return new RedirectResponse(
            $this->container->get('router')->generate('cocorico_user_resetting_check_email')
        );
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }

    /**
     * Tell the user to check his email provider
     *
     * @Route("/password-resetting-check-email", name="cocorico_user_resetting_check_email")
     *
     * @return RedirectResponse
     */
    public function checkEmailAction()
    {
        $session = $this->container->get('session');
        $email = $session->get(static::SESSION_EMAIL);
        $session->remove(static::SESSION_EMAIL);

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse(
                $this->container->get('router')->generate('cocorico_user_resetting_request')
            );
        }

        return $this->container->get('templating')->renderResponse(
            'CocoricoUserBundle:Frontend/Resetting:checkEmail.html.twig',
            array(
                'email' => $email,
            )
        );
    }

    /**
     * Reset user password
     *
     * @Route("/password-resetting-reset/{token}", name="cocorico_user_resetting_reset")
     *
     * @param string $token
     *
     * @return RedirectResponse|Response
     */
    public function resetAction($token)
    {
        $user = $this->container->get('cocorico_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new RedirectResponse($this->container->get('router')->generate('cocorico_user_resetting_request'));
        }

        $form = $this->container->get('fos_user.resetting.form');
        $formHandler = $this->container->get('fos_user.resetting.form.handler');
        $process = $formHandler->process($user);

        if ($process) {
            $this->container->get('session')->getFlashBag()->add(
                'success',
                $this->container->get('translator')->trans('user.resetting.success', array(), 'cocorico_user')
            );
            $url = $this->container->get('router')->generate(
                'cocorico_user_profile_show',
                array('id' => $user->getId())
            );
            $response = new RedirectResponse($url);
            $this->authenticateUser($user, $response);

            return $response;
        }

        return $this->container->get('templating')->renderResponse(
            'CocoricoUserBundle:Frontend/Resetting:reset.html.twig',
            array(
                'token' => $token,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Authenticate a user with Symfony Security
     *
     * @param \FOS\UserBundle\Model\UserInterface        $user
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function authenticateUser(UserInterface $user, Response $response)
    {
        try {
            $this->container->get('fos_user.security.login_manager')->loginUser(
                $this->container->getParameter('fos_user.firewall_name'),
                $user,
                $response
            );
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

}

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

use Cocorico\UserBundle\Form\Type\LoginFormType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

/**
 * Class SecurityController
 *
 */
class SecurityController extends Controller implements TranslationContainerInterface
{
    /**
     * @Route("/login", name="cocorico_user_login")
     *
     * @param Request $request
     *
     * @return Response
     * @throws AuthenticationCredentialsNotFoundException
     */
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('cocorico_home');
        }

        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $form = $this->createLoginForm($lastUsername);

        if ($error) {
            $translator = $this->get('translator');
            $error = $error->getMessage();
            $session->getFlashBag()->add(
                'error',
                /** @Ignore */
                $translator->trans($error, array(), 'cocorico_user')
            );
        }

        return $this->render(
            '@CocoricoUser/Frontend/Security/login.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param $lastUsername
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createLoginForm($lastUsername)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            LoginFormType::class,
            null,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_login_check'),
                'username' => $lastUsername
            )
        );

        return $form;
    }


    /**
     * Login check
     *
     * @Route("/login-check", name="cocorico_user_login_check")
     *
     * @throws \RuntimeException
     */
    public function checkAction()
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.'
        );
    }

    /**
     * Logout user
     *
     * @Route("/logout", name="cocorico_user_logout")
     *
     * @throws \RuntimeException
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message('Bad credentials.', 'cocorico_user');

        return $messages;
    }

}

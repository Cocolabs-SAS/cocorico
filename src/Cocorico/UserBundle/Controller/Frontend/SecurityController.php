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
use FOS\UserBundle\Controller\SecurityController as BaseController;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Security;

/**
 * Class SecurityController
 *
 */
class SecurityController extends BaseController implements TranslationContainerInterface
{
    /**
     *
     * @Route("/login", name="cocorico_user_login")
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $session = $this->container->get('session');
        $request = $this->container->get('request');
        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (!$session->has('profile')) {
                $session->set('profile', 'asker');
            }
            $url = $request->get('redirect_to') ? $request->get('redirect_to') :
                $this->container->get('router')->generate('cocorico_home');

            $response = new RedirectResponse($url);
        } else {
            $form = $this->createLoginForm();
            //$form->handleRequest($request);

            // get the error if any (works with forward and redirect -- see below)
            if ($request->attributes->has(Security::ACCESS_DENIED_ERROR)) {
                $error = $request->attributes->get(Security::ACCESS_DENIED_ERROR);
            } elseif (null !== $session && $session->has(Security::ACCESS_DENIED_ERROR)) {
                $error = $session->get(Security::ACCESS_DENIED_ERROR);
                $session->remove(Security::ACCESS_DENIED_ERROR);
            } elseif ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
                $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
            } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
                $error = $session->get(Security::AUTHENTICATION_ERROR);
                $session->remove(Security::AUTHENTICATION_ERROR);
            } else {
                $error = '';
            }

            $translator = $this->container->get('translator');
            if ($error) {
                // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
                $error = $error->getMessage();
                $session->getFlashBag()->add(
                    'error',
                    /** @Ignore */
                    $translator->trans($error, array(), 'cocorico_user')
                );
            }

            $response = $this->container->get('templating')->renderResponse(
                '@CocoricoUser/Frontend/Security/login.html.twig',
                array(
                    'form' => $form->createView(),
                )
            );
        }

        return $response;
    }

    /**
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createLoginForm()
    {
        $form = $this->container->get('form.factory')->createNamed(
            '',
            new LoginFormType(),
            null,
            array(
                'method' => 'POST',
                'action' => $this->container->get('router')->generate('cocorico_user_login_check'),
            )
        );

        return $form;
    }


    /**
     * Login check
     *
     * @Route("/login-check", name="cocorico_user_login_check")
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return \Symfony\Component\HttpFoundation\Response
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

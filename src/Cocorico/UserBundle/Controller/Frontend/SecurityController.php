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
     */
    public function loginAction(Request $request)
    {
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

        $csrfToken = $this->has('security.csrf.token_manager')
            ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
            : null;


        $form = $this->createLoginForm();

        return $this->renderLogin(
            array(
                'last_username' => $lastUsername,
                'error' => $error,
                'csrf_token' => $csrfToken,
                'form' => $form->createView(),
            )
        );
    }


//    /**
//     *
//     * @Route("/login", name="cocorico_user_login")
//     *
//     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     */
//    public function loginAction()
//    {
//        $session = $this->container->get('session');
//        $request = $this->container->get('request');
//        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            if (!$session->has('profile')) {
//                $session->set('profile', 'asker');
//            }
//            $url = $request->get('redirect_to') ? $request->get('redirect_to') :
//                $this->container->get('router')->generate('cocorico_home');
//
//            $response = new RedirectResponse($url);
//        } else {
//            $form = $this->createLoginForm();
//            //$form->handleRequest($request);
//
//            // get the error if any (works with forward and redirect -- see below)
//            if ($request->attributes->has(Security::ACCESS_DENIED_ERROR)) {
//                $error = $request->attributes->get(Security::ACCESS_DENIED_ERROR);
//            } elseif (null !== $session && $session->has(Security::ACCESS_DENIED_ERROR)) {
//                $error = $session->get(Security::ACCESS_DENIED_ERROR);
//                $session->remove(Security::ACCESS_DENIED_ERROR);
//            } elseif ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
//                $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
//            } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
//                $error = $session->get(Security::AUTHENTICATION_ERROR);
//                $session->remove(Security::AUTHENTICATION_ERROR);
//            } else {
//                $error = '';
//            }
//
//            $translator = $this->container->get('translator');
//            if ($error) {
//                // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
//                $error = $error->getMessage();
//                $session->getFlashBag()->add(
//                    'error',
//                    /** @Ignore */
//                    $translator->trans($error, array(), 'cocorico_user')
//                );
//            }
//
//            $response = $this->container->get('templating')->renderResponse(
//                '@CocoricoUser/Frontend/Security/login.html.twig',
//                array(
//                    'form' => $form->createView(),
//                )
//            );
//        }
//
//        return $response;
//    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return Response
     */
    protected function renderLogin(array $data)
    {
        return $this->render('@CocoricoUser/Frontend/Security/login.html.twig', $data);
    }

    /**
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createLoginForm()
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            LoginFormType::class,
            null,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_login_check'),
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

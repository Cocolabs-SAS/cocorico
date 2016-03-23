<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Authentication\Handler;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class LoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @param HttpUtils $httpUtils
     * @param array     $options
     */
    public function __construct(HttpUtils $httpUtils, array $options)
    {
        parent::__construct($httpUtils, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $session = $request->getSession();
        /** @var User $user */
        $user = $token->getUser();
        $cookies = $request->cookies;

        if ($cookies->has('userType')) {
            $userType = $cookies->get('userType');
        } else {
            $userType = 'asker';
            if ($user && $user->getListings()->count()) {
                $userType = 'offerer';
            }
        }

        $response = parent::onAuthenticationSuccess($request, $token);
        $session->set('profile', $userType);
        $response->headers->setCookie(new Cookie('userType', $userType, 0, '/', null, false, false));

        return $response;
    }
}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class UserRequestListener
{
    protected $securityTokenStorage;
    protected $timezone;

    public function __construct(TokenStorage $securityTokenStorage, $timezone)
    {
        $this->securityTokenStorage = $securityTokenStorage;
        $this->timezone = $timezone;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $cookies = $request->cookies;

        $session->set('timezone', $this->timezone);
        $user = $this->securityTokenStorage->getToken() ? $this->securityTokenStorage->getToken()->getUser() : null;
        if ($user) {
            if ($user && method_exists($user, "getTimeZone")) {
                $session->set('timezone', $user->getTimeZone());
            }
        }

        if ($cookies->has('userType') && $cookies->get('userType') == "offerer") {
            $session->set('profile', 'offerer');
        } elseif ($cookies->has('userType') && $cookies->get('userType') == "asker") {
            $session->set('profile', 'asker');
        }
    }
}
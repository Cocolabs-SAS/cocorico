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
use Ramsey\Uuid\Uuid;

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

        // If user can be offerer, he can switch between roles
        if ($user && method_exists($user, "isAdmin")) {
            $session->set('isAdmin', $user->isAdmin());
        } else {
            $session->set('isAdmin', false);
        }
        if ($user && method_exists($user, "canBeOfferer")) {
            #$session->set('canSwitch', $user->canBeOfferer());
            $session->set('canSwitch', False);
            $session->set('canPublish', $user->canPublish());
            $session->set('canAskForQuote', $user->canAskForQuote());
        } else {
            $session->set('canSwitch', False);
            $session->set('canPublish', False);
            $session->set('canAskForQuote', True);
        }

        // Tracking data
        if ($user && method_exists($user, "getId")) {
            $session->set('userId', $user->getId());
            $session->set('userType', $user->getPersonType());
        }
        $session_id = $session->get('uuid', Uuid::uuid4()->toString());
        $session->set('uuid', $session_id);

        if ($cookies->has('userType') && $cookies->get('userType') == "offerer" && $user && method_exists($user, "canBeOfferer") && $user->canBeOfferer()) {
            $session->set('profile', 'offerer');
        // } elseif ($cookies->has('userType') && $cookies->get('userType') == "asker") {
        } elseif ($cookies->has('userType') == "asker") {
            $session->set('profile', 'asker');
        }
    }
}

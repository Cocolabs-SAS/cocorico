<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UserTypeCookieListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $cookies = $request->cookies;
        if ($cookies->has('userType') && $cookies->get('userType') == "offerer") {
            $session->set('profile', 'offerer');
        } elseif ($cookies->has('userType') && $cookies->get('userType') == "asker") {
            $session->set('profile', 'asker');
        }

    }
}
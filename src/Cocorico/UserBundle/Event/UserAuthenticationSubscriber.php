<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Event;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class UserAuthenticationSubscriber implements EventSubscriberInterface
{
    protected $session;
    protected $timezone;

    /**
     * UserAuthenticationSubscriber constructor.
     * @param Session $session
     * @param string  $timezone
     */
    public function __construct(Session $session, $timezone)
    {
        $this->session = $session;
        $this->timezone = $timezone;
    }

    //Redundant with Cocorico/UserBundle/Listener/UserRequestListener
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $timezone = $this->timezone;
        if ($user instanceof User && $user->getTimeZone()) {
            $timezone = $user->getTimeZone();
        }

        $this->session->set('timezone', $timezone);
    }

    /**
     * @return    array
     */
    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

}

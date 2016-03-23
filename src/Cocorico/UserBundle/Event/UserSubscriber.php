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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function onRegister(UserEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        //$user = $event->getUser();
    }


    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_REGISTER => array('onRegister', 1),
        );
    }

}
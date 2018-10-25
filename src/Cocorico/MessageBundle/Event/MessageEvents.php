<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Event;


class MessageEvents
{
    /**
     * The MESSAGE_POST_SEND event occurs after a new message has been send.
     *
     * This event allows you to add functionality (send mail, sms, ...) after a new message has been send.
     * The event listener method receives a Cocorico\MessageBundle\Event\MessageEvent instance.
     */
    const MESSAGE_POST_SEND = 'cocorico_message.post_send';
}
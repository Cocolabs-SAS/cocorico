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

use FOS\MessageBundle\Model\ThreadInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\Event;

class MessageEvent extends Event
{
    protected $thread;
    protected $recipient;
    protected $sender;

    /**
     * @param ThreadInterface $thread
     * @param UserInterface   $recipient
     * @param UserInterface   $sender
     */
    public function __construct(ThreadInterface $thread, UserInterface $recipient, UserInterface $sender)
    {
        $this->thread = $thread;
        $this->recipient = $recipient;
        $this->sender = $sender;
    }

    /**
     * @return ThreadInterface
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param ThreadInterface $thread
     */
    public function setThread(ThreadInterface $thread)
    {
        $this->thread = $thread;
    }

    /**
     * @return UserInterface
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return UserInterface
     */
    public function getSender()
    {
        return $this->sender;
    }
}

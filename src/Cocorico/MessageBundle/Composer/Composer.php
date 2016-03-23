<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Composer;

use Cocorico\MessageBundle\MessageBuilder\NewThreadMessageBuilder;
use Cocorico\MessageBundle\MessageBuilder\ReplyMessageBuilder;
use FOS\MessageBundle\Composer\ComposerInterface;
use FOS\MessageBundle\Model\ThreadInterface;
use FOS\MessageBundle\ModelManager\MessageManagerInterface;
use FOS\MessageBundle\ModelManager\ThreadManagerInterface;

/**
 * Factory for message builders
 *
 *
 */
class Composer implements ComposerInterface
{
    /**
     * Message manager
     *
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * Thread manager
     *
     * @var ThreadManagerInterface
     */
    protected $threadManager;

    public function __construct(MessageManagerInterface $messageManager, ThreadManagerInterface $threadManager)
    {
        $this->messageManager = $messageManager;
        $this->threadManager = $threadManager;
    }

    /**
     * Starts composing a message, starting a new thread
     *
     * @return NewThreadMessageBuilder
     */
    public function newThread()
    {
        $thread = $this->threadManager->createThread();
        $message = $this->messageManager->createMessage();

        return new NewThreadMessageBuilder($message, $thread);
    }

    /**
     * Starts composing a message in a reply to a thread
     *
     * @param ThreadInterface $thread
     * @return ReplyMessageBuilder
     */
    public function reply(ThreadInterface $thread)
    {
        $message = $this->messageManager->createMessage();

        return new ReplyMessageBuilder($message, $thread);
    }
}

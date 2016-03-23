<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\FormModel;

use FOS\MessageBundle\FormModel\AbstractMessage;
use FOS\MessageBundle\Model\ThreadInterface;

class ReplyMessage extends AbstractMessage
{
    /**
     * The thread we reply to
     *
     * @var ThreadInterface
     */
    protected $thread;

    /**
     * @return ThreadInterface
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param  ThreadInterface $thread
     * @return null
     */
    public function setThread(ThreadInterface $thread)
    {
        $this->thread = $thread;
    }
}

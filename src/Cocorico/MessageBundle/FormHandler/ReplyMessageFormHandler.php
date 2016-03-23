<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\FormHandler;

use Cocorico\MessageBundle\FormModel\ReplyMessage;
use FOS\MessageBundle\FormHandler\AbstractMessageFormHandler;
use FOS\MessageBundle\FormModel\AbstractMessage;
use FOS\MessageBundle\Model\MessageInterface;

class ReplyMessageFormHandler extends AbstractMessageFormHandler
{

    /**
     * Composes a message from the form data
     *
     * @param AbstractMessage $message
     * @return MessageInterface the composed message ready to be sent
     * @throws \InvalidArgumentException if the message is not a ReplyMessage
     */
    public function composeMessage(AbstractMessage $message)
    {
        if (!$message instanceof ReplyMessage) {
            throw new \InvalidArgumentException(
                sprintf('Message must be a ReplyMessage instance, "%s" given', get_class($message))
            );
        }

        return $this->composer->reply($message->getThread())
            ->setSender($this->getAuthenticatedParticipant())
            ->setBody($message->getBody())
            ->getMessage();
    }

}

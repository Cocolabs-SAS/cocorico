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

use Cocorico\MessageBundle\FormModel\NewThreadMessage;
use FOS\MessageBundle\FormHandler\AbstractMessageFormHandler;
use FOS\MessageBundle\FormModel\AbstractMessage;
use FOS\MessageBundle\Model\MessageInterface;

class NewThreadMessageFormHandler extends AbstractMessageFormHandler
{

    /**
     * Composes a message from the form data
     *
     * @param AbstractMessage $message
     * @return MessageInterface the composed message ready to be sent
     * @throws \InvalidArgumentException if the message is not a NewThreadMessage
     */
    public function composeMessage(AbstractMessage $message)
    {
        if (!$message instanceof NewThreadMessage) {
            throw new \InvalidArgumentException(
                sprintf('Message must be a NewThreadMessage instance, "%s" given', get_class($message))
            );
        }

        $newThread = $this->composer->newThread()
            ->setListing($message->getListing())
            ->setSubject($message->getListing()->getTitle())
            ->addRecipient($message->getRecipient())
            ->setSender($this->getAuthenticatedParticipant())
            ->setBody($message->getBody());

        return $newThread->getMessage();
    }

}

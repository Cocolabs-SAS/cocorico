<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\MessageBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;

/**
 * Interface MailerInterface
 *
 */
interface MailerInterface
{
    /**
     * Send new message notification email
     *
     * @param integer       $threadId
     * @param UserInterface $recipient
     * @param UserInterface $sender
     *
     * @return void
     */
    public function sendNewThreadMessageToUser($threadId, UserInterface $recipient, UserInterface $sender);

}

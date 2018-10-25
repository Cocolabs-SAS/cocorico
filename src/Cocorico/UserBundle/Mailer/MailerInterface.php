<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;

/**
 * Interface MailerInterface
 *
 */
interface MailerInterface
{
    /**
     * Send an email to a user after successful registration
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendAccountCreatedMessageToUser(UserInterface $user);

    /**
     * Send an email to a user to confirm his account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendAccountCreationConfirmationMessageToUser(UserInterface $user);

    /**
     * Send password resetting email
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendResettingEmailMessageToUser(UserInterface $user);
}

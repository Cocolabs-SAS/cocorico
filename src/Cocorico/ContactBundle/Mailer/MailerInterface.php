<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Mailer;

use Cocorico\ContactBundle\Entity\Contact;

interface MailerInterface
{
    /**
     * email is sent to admin
     *
     * @param Contact $contact
     *
     * @return void
     */
    public function sendContactMessage(Contact $contact);
}

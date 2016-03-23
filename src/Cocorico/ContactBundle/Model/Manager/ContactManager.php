<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Model\Manager;

use Cocorico\ContactBundle\Entity\Contact;
use Doctrine\ORM\EntityManager;

class ContactManager extends BaseManager
{
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param  Contact $contact
     * @return Contact
     */
    public function save(Contact $contact)
    {
        $this->persistAndFlush($contact);

        return $contact;
    }
}

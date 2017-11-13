<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Entity;

use Cocorico\ContactBundle\Model\BaseContact;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Contact
 *
 * @ORM\Entity(repositoryClass="Cocorico\ContactBundle\Repository\ContactRepository")
 *
 * @ORM\Table(name="contact",indexes={
 *    @ORM\Index(name="created_at_c_idx", columns={"createdAt"}),
 *  }))
 */
class Contact extends BaseContact
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;

    public function __construct()
    {

    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return (string)$this->getSubject();
    }
}

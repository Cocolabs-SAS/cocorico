<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CMSBundle\Entity;

use Cocorico\CMSBundle\Model\BaseFooter;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;


/**
 * Footer
 *
 * @ORM\Entity(repositoryClass="Cocorico\CMSBundle\Repository\FooterRepository")
 *
 * @ORM\Table(name="footer",indexes={
 *    @ORM\Index(name="footer_published_idx", columns={"published"}),
 *  })
 *
 */
class Footer extends BaseFooter
{
    use ORMBehaviors\Timestampable\Timestampable;
    use ORMBehaviors\Translatable\Translatable;

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

    public function getTitle()
    {
        return (string)$this->translate()->getTitle();
    }

    public function getLink()
    {
        return (string)$this->translate()->getLink();
    }

    public function getUrl()
    {
        return (string)$this->translate()->getUrl();
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}

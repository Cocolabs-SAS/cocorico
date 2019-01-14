<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Entity;

use Cocorico\PageBundle\Model\BasePage;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;


/**
 * Page
 *
 * @ORM\Entity(repositoryClass="Cocorico\PageBundle\Repository\PageRepository")
 *
 * @ORM\Table(name="page",indexes={
 *    @ORM\Index(name="published_idx", columns={"published"}),
 *    @ORM\Index(name="created_at_p_idx", columns={"createdAt"})
 *  })
 *
 */
class Page extends BasePage
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

    public function getDescription()
    {
        return (string)$this->translate()->getDescription();
    }

    public function getMetaTitle()
    {
        return (string)$this->translate()->getMetaTitle();
    }

    public function getMetaDescription()
    {
        return (string)$this->translate()->getMetaDescription();
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}

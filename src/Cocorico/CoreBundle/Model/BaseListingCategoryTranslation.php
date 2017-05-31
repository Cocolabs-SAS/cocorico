<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Entity\ListingCategoryTranslation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 *
 */
abstract class BaseListingCategoryTranslation
{
    /**
     * @var string
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    protected $name;

    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getSluggableFields()
    {
        return ['name', 'id'];
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

    /**
     * Set name
     *
     * @param  string $name
     * @return ListingCategoryTranslation
     */
    public function setName($name)
    {
        $this->name = ucfirst($name);

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}

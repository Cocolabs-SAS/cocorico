<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Model;

use Cocorico\PageBundle\Entity\PageTranslation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 *
 */
abstract class BasePageTranslation
{

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(name="meta_title", type="string", length=55, nullable=false)
     *
     * @var string
     */
    protected $metaTitle;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(name="meta_description", type="text", length=155, nullable=false)
     *
     * @var string
     */
    protected $metaDescription;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @Assert\Length(
     *      min = "3",
     *      max = "100",
     *      minMessage = "assert.min_length {{ limit }}",
     *      maxMessage = "assert.max_length {{ limit }}"
     * )
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     *
     * @var string
     */
    protected $title;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(name="description", type="text", length=16777215, nullable=false)
     *
     * @var string
     */
    protected $description;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getSluggableFields()
    {
        return ['title'];
    }

    /**
     * Set title
     *
     * @param  string $title
     * @return PageTranslation
     */
    public function setTitle($title)
    {
        $this->title = ucfirst($title);

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param  string $description
     * @return BasePageTranslation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set metaTitle
     *
     * @param  string $metaTitle
     * @return BasePageTranslation
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = ucfirst($metaTitle);

        return $this;
    }

    /**
     * Get metaTitle
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set metaDescription
     *
     * @param  string $metaDescription
     * @return BasePageTranslation
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

}

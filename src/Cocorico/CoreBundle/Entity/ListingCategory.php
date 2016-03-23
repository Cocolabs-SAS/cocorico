<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Model\BaseListingCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * ListingCategory
 *
 * @Gedmo\Tree(type="nested")
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\ListingCategoryRepository")
 *
 * @ORM\Table(name="listing_category")
 *
 */
class ListingCategory extends BaseListingCategory
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ListingCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="ListingCategory", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Cocorico\CoreBundle\Entity\Listing", mappedBy="categories")
     **/
    private $listings;

    public function __construct()
    {
        $this->listings = new ArrayCollection();
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
     * Set parent
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCategory $parent
     * @return ListingCategory
     */
    public function setParent(ListingCategory $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Cocorico\CoreBundle\Entity\ListingCategory
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add listings
     *
     * @param  \Cocorico\CoreBundle\Entity\Listing $listings
     * @return ListingCategory
     */
    public function addListing(Listing $listings)
    {
        $this->listings[] = $listings;

        return $this;
    }

    /**
     * Remove listings
     *
     * @param \Cocorico\CoreBundle\Entity\Listing $listings
     */
    public function removeListing(Listing $listings)
    {
        $this->listings->removeElement($listings);
    }

    /**
     * Get listings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * Add children
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCategory $children
     * @return ListingCategory
     */
    public function addChild(ListingCategory $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Cocorico\CoreBundle\Entity\ListingCategory $children
     */
    public function removeChild(ListingCategory $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getName()
    {
        return $this->translate()->getName();
    }

    public function __toString()
    {
        return $this->getName();
    }
}

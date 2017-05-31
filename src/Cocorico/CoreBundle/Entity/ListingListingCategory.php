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

use Cocorico\CoreBundle\Model\ListingCategoryFieldValueInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingListingCategory
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="listing_listing_category")
 *
 */
class ListingListingCategory
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Listing", inversedBy="listingListingCategories")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listing;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCategory", inversedBy="listingListingCategories")
     * @ORM\JoinColumn(name="listing_category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Model\ListingCategoryFieldValueInterface", mappedBy="listingListingCategory", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $values;


    public function __construct()
    {
        $this->values = new ArrayCollection();
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
     * Set listing
     *
     * @param  \Cocorico\CoreBundle\Entity\Listing $listing
     * @return ListingListingCategory
     */
    public function setListing(Listing $listing = null)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * Get listing
     *
     * @return \Cocorico\CoreBundle\Entity\Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return ListingCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param ListingCategory $category
     */
    public function setCategory(ListingCategory $category)
    {
        $this->category = $category;
    }


    /**
     * Add field value
     *
     * @param ListingCategoryFieldValueInterface $value
     * @return ListingListingCategory
     */
    public function addValue(ListingCategoryFieldValueInterface $value)
    {
        $value->setListingListingCategory($this);
        $this->values[] = $value;

        return $this;
    }

    /**
     * Remove field value
     *
     * @param  ListingCategoryFieldValueInterface $value
     */
    public function removeValue(ListingCategoryFieldValueInterface $value)
    {
        $this->values->removeElement($value);
    }

    /**
     * Get fields values
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|ListingCategoryFieldValueInterface[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getCategory()->getName();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            if ($this->getValues()) {
                foreach ($this->getValues() as $listingCategoryFieldValue) {
                    $listingCategoryFieldValue = clone $listingCategoryFieldValue;
                    $listingCategoryFieldValue->setListingListingCategory($this);
                    $this->addValue($listingCategoryFieldValue);
                }
            }
        }
    }
}

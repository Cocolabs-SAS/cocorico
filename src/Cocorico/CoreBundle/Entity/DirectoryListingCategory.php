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
 * @ORM\Table(name="directory_listing_category")
 *
 */
class DirectoryListingCategory
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
     * @ORM\ManyToOne(targetEntity="Directory", inversedBy="directoryListingCategories")
     * @ORM\JoinColumn(name="directory_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $directory;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCategory", inversedBy="directoryListingCategories")
     * @ORM\JoinColumn(name="listing_category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $category;

    /**
     * @var string
     * @ORM\Column(name="source", type="string", nullable=true)
     */
    private $source = 'C4';


    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Model\ListingCategoryFieldValueInterface", mappedBy="directoryListingCategory", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * Set source
     *
     * @param  string $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * Set directory
     *
     * @param  \Cocorico\CoreBundle\Entity\Directory $directory
     * @return ListingListingCategory
     */
    public function setDirectory(Directory $directory = null)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory
     *
     * @return \Cocorico\CoreBundle\Entity\Directory
     */
    public function getDirectory()
    {
        return $this->directory;
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
     * @return DirectoryListingCategory
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

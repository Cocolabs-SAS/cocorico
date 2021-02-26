<?php

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Repository\DirectoryCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DirectoryCategory
 * @ORM\Entity(repositoryClass=DirectoryCategoryRepository::class)
 *
 * @ORM\Table(name="directory_category",indexes={
 *    @ORM\Index(name="directory_idx", columns={"directory_id"}),
 *  })
 */
class DirectoryCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="directory_id", type="integer", nullable=true)
     * @var integer|null
     * Directory Id
     */
    private $directoryId;

    /**
     * @ORM\Column(name="sector_string", type="text", nullable=true)
     * @var string|null
     * Directory provided sector name
     */
    private $sectorString;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCategory", inversedBy="listingListingCategories")
     * @ORM\JoinColumn(name="listing_category_id", referencedColumnName="id", nullable=true)
     * Link to corresponding listing categories
     */
    private $category;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set directoryId.
     *
     * @param int|null $directoryId
     *
     * @return DirectoryCategory
     */
    public function setDirectoryId($directoryId = null)
    {
        $this->directoryId = $directoryId;

        return $this;
    }

    /**
     * Get directoryId.
     *
     * @return int|null
     */
    public function getDirectoryId()
    {
        return $this->directoryId;
    }

    /**
     * Set sectorString.
     *
     * @param string|null $sectorString
     *
     * @return DirectoryCategory
     */
    public function setSectorString($sectorString = null)
    {
        $this->sectorString = $sectorString;

        return $this;
    }

    /**
     * Get sectorString.
     *
     * @return string|null
     */
    public function getSectorString()
    {
        return $this->sectorString;
    }

    /**
     * Set category.
     *
     * @param \Cocorico\CoreBundle\Entity\ListingCategory|null $category
     *
     * @return DirectoryCategory
     */
    public function setCategory(\Cocorico\CoreBundle\Entity\ListingCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \Cocorico\CoreBundle\Entity\ListingCategory|null
     */
    public function getCategory()
    {
        return $this->category;
    }
}

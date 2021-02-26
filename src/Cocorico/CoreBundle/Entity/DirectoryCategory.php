<?php

namespace App\Entity;

use App\Repository\DirectoryCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DirectoryCategoryRepository::class)
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
}

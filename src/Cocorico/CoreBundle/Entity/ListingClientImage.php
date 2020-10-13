<?php

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Model\BaseListingImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingClientImage
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="listing_client_image", indexes={
 *    @ORM\Index(name="position_li_idx", columns={"position"})
 *  })
 *
 */
class ListingClientImage extends BaseListingImage
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
     * @ORM\ManyToOne(targetEntity="Listing", inversedBy="clientImages")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listing;

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
     * @return ListingImage
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

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }
}

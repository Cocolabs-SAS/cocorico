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

use Cocorico\CoreBundle\Model\BaseListingDiscount;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListingDiscount
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\ListingDiscountRepository")
 * @UniqueEntity(
 *     fields={"listing", "fromQuantity"},
 *     errorPath="fromQuantity",
 *     message="assert.unique"
 * )
 * @ORM\Table(name="listing_discount",
 *  indexes={
 *    @ORM\Index(name="discount_idx", columns={"discount"}),
 *    @ORM\Index(name="from_quantity_idx", columns={"from_quantity"})
 *  },
 *  uniqueConstraints={@ORM\UniqueConstraint(name="discount_unique", columns={"listing_id", "from_quantity"})}
 * )
 */
class ListingDiscount extends BaseListingDiscount
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
     * @ORM\ManyToOne(targetEntity="Listing", inversedBy="discounts")
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
     * @return ListingDiscount
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

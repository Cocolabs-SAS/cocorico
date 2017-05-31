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

use Cocorico\CoreBundle\Model\BaseListingListingCharacteristic;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingListingCharacteristic
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="listing_listing_characteristic")
 *
 */
class ListingListingCharacteristic extends BaseListingListingCharacteristic
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
     * @ORM\ManyToOne(targetEntity="Listing", inversedBy="listingListingCharacteristics")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listing;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCharacteristic", inversedBy="listingListingCharacteristics", fetch="EAGER")
     * @ORM\JoinColumn(name="listing_characteristic_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listingCharacteristic;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCharacteristicValue", inversedBy="listingListingCharacteristics", fetch="EAGER")
     * @ORM\JoinColumn(name="listing_characteristic_value_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $listingCharacteristicValue;


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
     * @return ListingListingCharacteristic
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
     * @return ListingCharacteristic
     */
    public function getListingCharacteristic()
    {
        return $this->listingCharacteristic;
    }

    /**
     * @param ListingCharacteristic $listingCharacteristic
     */
    public function setListingCharacteristic(ListingCharacteristic $listingCharacteristic)
    {
        $this->listingCharacteristic = $listingCharacteristic;
    }

    /**
     * @return ListingCharacteristicValue
     */
    public function getListingCharacteristicValue()
    {
        return $this->listingCharacteristicValue;
    }

    /**
     * @param ListingCharacteristicValue $listingCharacteristicValue
     */
    public function setListingCharacteristicValue(ListingCharacteristicValue $listingCharacteristicValue = null)
    {
        $this->listingCharacteristicValue = $listingCharacteristicValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getListingCharacteristic()->getName();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }
}

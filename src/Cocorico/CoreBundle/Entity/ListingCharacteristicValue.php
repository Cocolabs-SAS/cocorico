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

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListingCharacteristicValue
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\ListingCharacteristicValueRepository")
 *
 * @ORM\Table(name="listing_characteristic_value",indexes={
 *    @ORM\Index(name="position_lcv_idx", columns={"position"})
 *  })
 *
 */
class ListingCharacteristicValue
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(name="position", type="smallint", nullable=false)
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCharacteristicType", inversedBy="listingCharacteristicValues")
     * @ORM\JoinColumn(name="listing_characteristic_type_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listingCharacteristicType;

    /**
     *
     * @ORM\OneToMany(targetEntity="ListingListingCharacteristic", mappedBy="listingCharacteristicValue", cascade={"persist", "remove"})
     *
     */
    private $listingListingCharacteristics;

    /**
     * Constructor
     */
    public function __construct()
    {

    }


    /**
     * Translation proxy
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
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
     * Set position
     *
     * @param  boolean $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return boolean
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get characteristic type
     *
     * @return ListingCharacteristicType
     */
    public function getListingCharacteristicType()
    {
        return $this->listingCharacteristicType;
    }

    /**
     * Set characteristic type
     *
     * @param ListingCharacteristicType $listingCharacteristicType
     * @return $this
     */
    public function setListingCharacteristicType(ListingCharacteristicType $listingCharacteristicType = null)
    {
        $this->listingCharacteristicType = $listingCharacteristicType;

        return $this;
    }

    /**
     * Add listingListingCharacteristics
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristics
     * @return ListingCharacteristicValue
     */
    public function addListingListingCharacteristic(ListingListingCharacteristic $listingListingCharacteristics)
    {
        $this->listingListingCharacteristics[] = $listingListingCharacteristics;

        return $this;
    }

    /**
     * Remove listingListingCharacteristics
     *
     * @param \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristics
     */
    public function removeListingListingCharacteristic(ListingListingCharacteristic $listingListingCharacteristics)
    {
        $this->listingListingCharacteristics->removeElement($listingListingCharacteristics);
    }

    /**
     * Get listingListingCharacteristics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListingListingCharacteristics()
    {
        return $this->listingListingCharacteristics;
    }
}

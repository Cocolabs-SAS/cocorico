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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListingCharacteristicType
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="listing_characteristic_type")
 *
 */
class ListingCharacteristicType
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
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(type="string", length=255, name="name", nullable=false)
     *
     * @var string $name
     */
    protected $name;

    /**
     *
     * @ORM\OneToMany(targetEntity="ListingCharacteristic", mappedBy="listingCharacteristicType", cascade={"persist", "remove"})
     */
    private $listingCharacteristics;

    /**
     *
     * @ORM\OneToMany(targetEntity="ListingCharacteristicValue", mappedBy="listingCharacteristicType", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "asc"})
     */
    private $listingCharacteristicValues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->listingCharacteristics = new ArrayCollection();
        $this->listingCharacteristicValues = new ArrayCollection();
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
     * Add characteristic value
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCharacteristicValue $listingCharacteristicValue
     * @return ListingCharacteristic
     */
    public function addListingCharacteristicValue(ListingCharacteristicValue $listingCharacteristicValue)
    {
        $listingCharacteristicValue->setListingCharacteristicType($this);
        $this->listingCharacteristicValues[] = $listingCharacteristicValue;

        return $this;
    }

    /**
     * Remove characteristic value
     *
     * @param \Cocorico\CoreBundle\Entity\ListingCharacteristicValue $listingCharacteristicValue
     */
    public function removeListingCharacteristicValue(ListingCharacteristicValue $listingCharacteristicValue)
    {
        $this->listingCharacteristicValues->removeElement($listingCharacteristicValue);
        $listingCharacteristicValue->setListingCharacteristicType(null);
    }

    /**
     * Get characteristic value
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListingCharacteristicValues()
    {
        return $this->listingCharacteristicValues;
    }

    /**
     * Add listingCharacteristics
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCharacteristic $listingCharacteristics
     * @return ListingCharacteristicType
     */
    public function addListingCharacteristic(ListingCharacteristic $listingCharacteristics)
    {
        $this->listingCharacteristics[] = $listingCharacteristics;

        return $this;
    }

    /**
     * Remove listingCharacteristics
     *
     * @param \Cocorico\CoreBundle\Entity\ListingCharacteristic $listingCharacteristics
     */
    public function removeListingCharacteristic(ListingCharacteristic $listingCharacteristics)
    {
        $this->listingCharacteristics->removeElement($listingCharacteristics);
    }

    /**
     * Get listingCharacteristics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListingCharacteristics()
    {
        return $this->listingCharacteristics;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return ListingCharacteristicType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }
}

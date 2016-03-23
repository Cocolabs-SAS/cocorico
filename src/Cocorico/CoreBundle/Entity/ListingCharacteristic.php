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

use Cocorico\CoreBundle\Model\BaseListingCharacteristic;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * ListingCharacteristic
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\ListingCharacteristicRepository")
 *
 * @ORM\Table(name="listing_characteristic",indexes={
 *    @ORM\Index(name="position_idx", columns={"position"})
 *  })
 *
 */
class ListingCharacteristic extends BaseListingCharacteristic
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
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCharacteristicType", inversedBy="listingCharacteristics")
     * @ORM\JoinColumn(name="listing_characteristic_type_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listingCharacteristicType;

    /**
     *
     * @ORM\OneToMany(targetEntity="ListingListingCharacteristic", mappedBy="listingCharacteristic", cascade={"persist", "remove"})
     */
    private $listingListingCharacteristics;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCharacteristicGroup", inversedBy="listingCharacteristics", fetch="EAGER")
     * @ORM\JoinColumn(name="listing_characteristic_group_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listingCharacteristicGroup;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->listingListingCharacteristics = new ArrayCollection();
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

    public function getListingCharacteristicTypeValues()
    {
        return $this->getListingCharacteristicType()->getListingCharacteristicValues();
    }

    /**
     * Add characteristics
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristic
     * @return ListingCharacteristic
     */
    public function addListingListingCharacteristic(ListingListingCharacteristic $listingListingCharacteristic)
    {
        $this->listingListingCharacteristics[] = $listingListingCharacteristic;

        return $this;
    }

    /**
     * Remove characteristics
     *
     * @param \Cocorico\CoreBundle\Entity\ListingListingCharacteristic $listingListingCharacteristic
     */
    public function removeListingListingCharacteristic(ListingListingCharacteristic $listingListingCharacteristic)
    {
        $this->listingListingCharacteristics->removeElement($listingListingCharacteristic);
    }

    /**
     * Get characteristics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListingListingCharacteristics()
    {
        return $this->listingListingCharacteristics;
    }

    /**
     * Set listingCharacteristicType
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCharacteristicType $listingCharacteristicType
     * @return ListingCharacteristic
     */
    public function setListingCharacteristicType(ListingCharacteristicType $listingCharacteristicType)
    {
        $this->listingCharacteristicType = $listingCharacteristicType;

        return $this;
    }

    /**
     * Get listingCharacteristicType
     *
     * @return \Cocorico\CoreBundle\Entity\ListingCharacteristicType
     */
    public function getListingCharacteristicType()
    {
        return $this->listingCharacteristicType;
    }

    /**
     * Set ListingCharacteristicGroup
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingCharacteristicGroup $listingCharacteristicGroup
     * @return ListingCharacteristic
     */
    public function setListingCharacteristicGroup(ListingCharacteristicGroup $listingCharacteristicGroup)
    {
        $this->listingCharacteristicGroup = $listingCharacteristicGroup;

        return $this;
    }

    /**
     * Get ListingCharacteristicGroup
     *
     * @return \Cocorico\CoreBundle\Entity\ListingCharacteristicGroup
     */
    public function getListingCharacteristicGroup()
    {
        return $this->listingCharacteristicGroup;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->translate()->getName();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->translate()->getName();
    }
}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Document;

use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document(
 *      collection="listing_availabilities",
 *      repositoryClass="Cocorico\CoreBundle\Repository\ListingAvailabilityRepository"
 * )
 * @MongoDB\UniqueIndex(keys={"listingId"="asc", "day"="asc"})
 *
 * @CocoricoAssert\ListingAvailability()
 */
class ListingAvailability
{
    const STATUS_AVAILABLE = 1;
    const STATUS_UNAVAILABLE = 2;
    const STATUS_BOOKED = 3;

    public static $statusValues = array(
        self::STATUS_AVAILABLE => 'entity.listing_availability.status.available',
        self::STATUS_UNAVAILABLE => 'entity.listing_availability.status.unavailable',
        self::STATUS_BOOKED => 'entity.listing_availability.status.booked'
    );

    public static $visibleValues = array(
        self::STATUS_AVAILABLE => 'entity.listing_availability.status.available',
        self::STATUS_UNAVAILABLE => 'entity.listing_availability.status.unavailable'
    );

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="int", nullable="false", name="lId")
     * @Assert\NotBlank()
     */
    protected $listingId;

    /**
     * @MongoDB\Field(type="date", nullable="false", name="d")
     * @MongoDB\Index(order="asc")
     * @Assert\NotBlank()
     */
    protected $day;


    /**
     * @MongoDB\Field(type="int", nullable="false", name="s")
     * @MongoDB\Index(order="asc")
     * @Assert\Choice(callback = "getStatusValuesKeys")
     */
    protected $status;

    /**
     * @MongoDB\Field(type="int", nullable="false", name="p")
     * @MongoDB\Index(order="asc")
     * @Assert\NotBlank()
     */
    protected $price;

    /**
     * @MongoDB\EmbedMany(targetDocument="ListingAvailabilityTime", name="ts")
     *
     * @var ArrayCollection $times
     */
    protected $times;


    /**
     * //Gedmo\ReferenceOne(type="entity", class="Cocorico\CoreBundle\Entity\Listing", inversedBy="listingAvailabilities", identifier="listingId")
     */
//    protected $listing;

    public function __construct()
    {
        $this->times = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set listingId
     *
     * @param int $listingId
     * @return self
     */
    public function setListingId($listingId)
    {
        $this->listingId = intval($listingId);

        return $this;
    }

    /**
     * Get listingId
     *
     * @return int $listingId
     */
    public function getListingId()
    {
        return $this->listingId;
    }

    /**
     * Set day
     *
     * @param \DateTime $day
     * @return self
     */
    public function setDay($day)
    {
//        $day->setTimestamp($day->getTimestamp() - 3600);
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return \DateTime $day
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return self
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for availability.status : %s.', $status)
            );
        }
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusText()
    {
        return self::$statusValues[$this->getStatus()];
    }

    /**
     * Get Status Keys
     *
     * @return string
     */
    public static function getStatusValuesKeys()
    {
        return array_keys(self::$statusValues);
    }


    /**
     * Add time
     *
     * @param ListingAvailabilityTime $time
     */
    public function addTime(ListingAvailabilityTime $time)
    {
        $this->times[] = $time;
    }

    /**
     * Remove time
     *
     * @param ListingAvailabilityTime $time
     */
    public function removeTime(ListingAvailabilityTime $time)
    {
        $this->times->removeElement($time);
    }

    /**
     * Get times
     *
     * @return ArrayCollection|ListingAvailabilityTime[] $times
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * Set times
     *
     * @param  ArrayCollection $times
     *
     * @return ArrayCollection|ListingAvailabilityTime[] $times
     */
    public function setTimes(ArrayCollection $times)
    {
        $this->times = $times;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getPriceDecimal()
    {
        return $this->price / 100;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            //Times
            $times = $this->getTimes();
            $this->times = new ArrayCollection();
            foreach ($times as $time) {
                $this->addTime(clone $time);
            }
        }
    }
}

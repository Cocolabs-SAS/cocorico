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


use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\EmbeddedDocument()
 * //MongoDB\UniqueIndex(keys={"day"="asc", "id"="asc"})
 */
class ListingAvailabilityTime
{
    const STATUS_AVAILABLE = 1;
    const STATUS_UNAVAILABLE = 2;
    const STATUS_BOOKED = 3;

    public static $statusValues = array(
        self::STATUS_AVAILABLE => 'entity.listing_availability.status.available',
        self::STATUS_UNAVAILABLE => 'entity.listing_availability.status.unavailable',
        self::STATUS_BOOKED => 'entity.listing_availability.status.booked'
    );

    /**
     * The minute number in the day
     * @MongoDB\Id(strategy="NONE", type="int", name="_id")
     */
    protected $id;

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
     * Get id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param  $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }


}

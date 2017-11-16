<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Entity\ListingLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListingLocation
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseListingLocation
{

    /**
     * @ORM\Column(name="country", type="string", length=3, nullable=false)
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @var string
     */
    protected $country;

    /**
     * @ORM\Column(name="city", type="string", length=75, nullable=false)
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(name="zip", type="string", length=20, nullable=true)
     *
     * @var string
     */
    protected $zip;

    /**
     * @ORM\Column(name="route", type="string", length=120, nullable=true)
     *
     * @var string
     */
    protected $route;

    /**
     * @ORM\Column(name="street_number", type="string", length=20, nullable=true)
     *
     * @var string
     */
    protected $streetNumber;

    public function __construct()
    {

    }

    /**
     * Set country
     *
     * @param  string $country
     * @return ListingLocation
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param  string $city
     * @return ListingLocation
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param  string $zip
     * @return ListingLocation
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set route
     *
     * @param  string $route
     * @return ListingLocation
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set streetNumber
     *
     * @param  string $streetNumber
     * @return ListingLocation
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * Get streetNumber
     *
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }
}

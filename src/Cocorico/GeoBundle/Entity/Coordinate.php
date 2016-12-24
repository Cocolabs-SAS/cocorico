<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\GeoBundle\Entity;

use Cocorico\CoreBundle\Entity\ListingLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Coordinate
 *
 * @ORM\Entity(repositoryClass="Cocorico\GeoBundle\Repository\CoordinateRepository")
 *
 * @ORM\Table(name="geo_coordinate",indexes={
 *    @ORM\Index(name="coordinate_idx", columns={"lat", "lng"})
 *  }
 * )
 *
 */
class Coordinate
{
    use ORMBehaviors\Timestampable\Timestampable;
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="lat", type="decimal", precision=11, scale=7, nullable=false)
     */
    protected $lat;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="lng", type="decimal", precision=11, scale=7, nullable=false)
     */
    protected $lng;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Country", inversedBy="coordinates", cascade={"persist"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     *
     * @var $country
     */
    protected $country;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Area", inversedBy="coordinates", cascade={"persist"})
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=true)
     *
     * @var $area
     */
    protected $area;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Department", inversedBy="coordinates", cascade={"persist"})
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", nullable=true)
     *
     * @var $department
     */
    protected $department;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\City", inversedBy="coordinates", cascade={"persist"})
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=true)
     *
     * @var $city
     */
    protected $city;

    /**
     * @ORM\Column(name="zip", type="string", length=30, nullable=true)
     *
     * @var string
     */
    protected $zip;

    /**
     * @ORM\Column(name="route", type="string", length=200, nullable=true)
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

    /**
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=false)
     *
     * @var string $address formatted address
     */
    protected $address;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\ListingLocation", mappedBy="coordinate", cascade={"persist", "remove"})
     **/
    protected $listingLocations;

    public function __construct()
    {

        $this->listingLocations = new ArrayCollection();
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
     * Set country
     *
     * @param  \Cocorico\GeoBundle\Entity\Country $country
     * @return Coordinate
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \Cocorico\GeoBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set area
     *
     * @param  \Cocorico\GeoBundle\Entity\Area $area
     * @return Coordinate
     */
    public function setArea(Area $area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return \Cocorico\GeoBundle\Entity\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set lat
     *
     * @param  string $lat
     * @return Coordinate
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param  string $lng
     * @return Coordinate
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return string
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set zip
     *
     * @param  string $zip
     * @return Coordinate
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
     * @return Coordinate
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
     * @return Coordinate
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

    /**
     * Set address
     *
     * @param  string $address
     * @return Coordinate
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set department
     *
     * @param  \Cocorico\GeoBundle\Entity\Department $department
     * @return Coordinate
     */
    public function setDepartment(Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \Cocorico\GeoBundle\Entity\Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set city
     *
     * @param  \Cocorico\GeoBundle\Entity\City $city
     * @return Coordinate
     */
    public function setCity(City $city = null)
    {
        $this->city = $city;
        $city->addCoordinate($this);

        return $this;
    }

    /**
     * Get city
     *
     * @return \Cocorico\GeoBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Add listingLocation
     *
     * @param  \Cocorico\CoreBundle\Entity\ListingLocation $listingLocation
     * @return Coordinate
     */
    public function addListingLocation(ListingLocation $listingLocation)
    {
        if (!$this->listingLocations->contains($listingLocation)) {
            $this->listingLocations[] = $listingLocation;
            $listingLocation->setCoordinate($this);
        }

        return $this;
    }

    /**
     * Remove listingLocations
     *
     * @param \Cocorico\CoreBundle\Entity\ListingLocation $listingLocation
     */
    public function removeListingLocation(ListingLocation $listingLocation)
    {
        $this->listingLocations->removeElement($listingLocation);
    }

    /**
     * Get listingLocations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListingLocations()
    {
        return $this->listingLocations;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }
}

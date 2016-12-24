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

use Cocorico\GeoBundle\Model\GeocodableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Area
 *
 * @ORM\Entity(repositoryClass="Cocorico\GeoBundle\Repository\AreaRepository")
 *
 * @ORM\Table(name="geo_area")
 *
 */
class Area
{
    use ORMBehaviors\Translatable\Translatable;
    use GeocodableTrait;

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
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Country", inversedBy="areas", cascade={"persist"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var country
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\Department", mappedBy="area", cascade={"persist", "remove"})
     **/
    private $departments;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\City", mappedBy="area", cascade={"persist", "remove"})
     **/
    private $cities;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\Coordinate", mappedBy="area", cascade={"persist", "remove"})
     **/
    private $coordinates;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
        $this->cities = new ArrayCollection();
        $this->coordinates = new ArrayCollection();
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
     * Set country
     *
     * @param  \Cocorico\GeoBundle\Entity\Country $country
     * @return Area
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
     * Add coordinate
     *
     * @param  \Cocorico\GeoBundle\Entity\Coordinate $coordinate
     * @return Area
     */
    public function addCoordinate(Coordinate $coordinate)
    {
        if (!$this->coordinates->contains($coordinate)) {
            $this->coordinates[] = $coordinate;
            $coordinate->setArea($this);
        }

        return $this;
    }

    /**
     * Remove coordinate
     *
     * @param \Cocorico\GeoBundle\Entity\Coordinate $coordinate
     */
    public function removeCoordinate(Coordinate $coordinate)
    {
        $this->coordinates->removeElement($coordinate);
    }

    /**
     * Get coordinates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * Add department
     *
     * @param  \Cocorico\GeoBundle\Entity\Department $department
     * @return Area
     */
    public function addDepartment(Department $department)
    {
        if (!$this->departments->contains($department)) {
            $this->departments[] = $department;
            $department->setArea($this);
        }

        return $this;
    }

    /**
     * Remove department
     *
     * @param \Cocorico\GeoBundle\Entity\Department $department
     */
    public function removeDepartment(Department $department)
    {
        $this->departments->removeElement($department);
    }

    /**
     * Get departments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * Add city
     *
     * @param  \Cocorico\GeoBundle\Entity\City $city
     * @return Area
     */
    public function addCity(City $city)
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
            $city->setArea($this);
        }

        return $this;
    }

    /**
     * Remove city
     *
     * @param \Cocorico\GeoBundle\Entity\City $city
     */
    public function removeCity(City $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->translate()->getName();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}

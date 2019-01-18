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
 * Department
 *
 * @ORM\Entity(repositoryClass="Cocorico\GeoBundle\Repository\DepartmentRepository")
 *
 * @ORM\Table(name="geo_department")
 *
 */
class Department
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
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Country", inversedBy="departments", cascade={"persist"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var country
     */
    private $country;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Area", inversedBy="departments", cascade={"persist"})
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var area
     */
    private $area;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\City", mappedBy="department", cascade={"persist", "remove"})
     **/
    private $cities;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\Coordinate", mappedBy="department", cascade={"persist", "remove"})
     **/
    private $coordinates;

    public function __construct()
    {
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
     * @return $this
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
     * @return $this
     */
    public function addCoordinate(Coordinate $coordinate)
    {
        if (!$this->coordinates->contains($coordinate)) {
            $this->coordinates[] = $coordinate;
            $coordinate->setDepartment($this);
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
     * Set area
     *
     * @param  \Cocorico\GeoBundle\Entity\Area $area
     * @return Department
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
     * Add city
     *
     * @param  \Cocorico\GeoBundle\Entity\City $city
     * @return Department
     */
    public function addCity(City $city)
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
            $city->setDepartment($this);
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

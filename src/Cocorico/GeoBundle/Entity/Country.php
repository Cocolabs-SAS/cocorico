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
 * Country
 *
 * @ORM\Entity(repositoryClass="Cocorico\GeoBundle\Repository\CountryRepository")
 *
 * @ORM\Table(name="geo_country",indexes={
 *    @ORM\Index(name="code_idx", columns={"code"})
 *  })
 *
 */
class Country
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
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="code", type="string", length=3, nullable=false)
     */
    protected $code;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\Area", mappedBy="country", cascade={"persist", "remove"})
     **/
    private $areas;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\Department", mappedBy="country", cascade={"persist", "remove"})
     **/
    private $departments;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\City", mappedBy="country", cascade={"persist", "remove"})
     **/
    private $cities;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\Coordinate", mappedBy="country", cascade={"persist", "remove"})
     **/
    private $coordinates;

    public function __construct()
    {
        $this->areas = new ArrayCollection();
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
     * Set code
     *
     * @param  string $code
     * @return Country
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add areas
     *
     * @param  \Cocorico\GeoBundle\Entity\Area $area
     * @return Country
     */
    public function addArea(Area $area)
    {
        if (!$this->areas->contains($area)) {
            $this->areas[] = $area;
            $area->setCountry($this);
        }

        return $this;
    }

    /**
     * Remove area
     *
     * @param \Cocorico\GeoBundle\Entity\Area $area
     */
    public function removeArea(Area $area)
    {
        $this->areas->removeElement($area);
    }

    /**
     * Get areas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * Add coordinates
     *
     * @param  \Cocorico\GeoBundle\Entity\Coordinate $coordinate
     * @return Country
     */
    public function addCoordinate(Coordinate $coordinate)
    {
        if (!$this->coordinates->contains($coordinate)) {
            $this->coordinates[] = $coordinate;
            $coordinate->setCountry($this);
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
     * @return Country
     */
    public function addDepartment(Department $department)
    {
        if (!$this->departments->contains($department)) {
            $this->departments[] = $department;
            $department->setCountry($this);
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
     * Add $city
     *
     * @param  \Cocorico\GeoBundle\Entity\City $city
     * @return Country
     */
    public function addCity(City $city)
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
            $city->setCountry($this);
        }

        return $this;
    }

    /**
     * Remove cities
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

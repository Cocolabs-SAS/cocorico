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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Country
 *
 * @ORM\Entity(repositoryClass="Cocorico\GeoBundle\Entity\CountryRepository")
 *
 * @ORM\Table(name="geo_country",indexes={
 *    @ORM\Index(name="code_idx", columns={"code"})
 *  })
 *
 */
class Country
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
     * @param  \Cocorico\GeoBundle\Entity\Area $areas
     * @return Country
     */
    public function addArea(Area $areas)
    {
        if (!$this->areas->contains($areas)) {
            $this->areas[] = $areas;
            $areas->setCountry($this);
        }

        return $this;
    }

    /**
     * Remove areas
     *
     * @param \Cocorico\GeoBundle\Entity\Area $areas
     */
    public function removeArea(Area $areas)
    {
        $this->areas->removeElement($areas);
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
     * @param  \Cocorico\GeoBundle\Entity\Coordinate $coordinates
     * @return Country
     */
    public function addCoordinate(Coordinate $coordinates)
    {
        if (!$this->coordinates->contains($coordinates)) {
            $this->coordinates[] = $coordinates;
            $coordinates->setCountry($this);
        }

        return $this;
    }

    /**
     * Remove coordinates
     *
     * @param \Cocorico\GeoBundle\Entity\Coordinate $coordinates
     */
    public function removeCoordinate(Coordinate $coordinates)
    {
        $this->coordinates->removeElement($coordinates);
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
     * Add departments
     *
     * @param  \Cocorico\GeoBundle\Entity\Department $departments
     * @return Country
     */
    public function addDepartment(Department $departments)
    {
        if (!$this->departments->contains($departments)) {
            $this->departments[] = $departments;
            $departments->setCountry($this);
        }

        return $this;
    }

    /**
     * Remove depatments
     *
     * @param \Cocorico\GeoBundle\Entity\Department $departments
     */
    public function removeDepartment(Department $departments)
    {
        $this->departments->removeElement($departments);
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
     * Add cities
     *
     * @param  \Cocorico\GeoBundle\Entity\City $cities
     * @return Country
     */
    public function addCity(City $cities)
    {
        if (!$this->cities->contains($cities)) {
            $this->cities[] = $cities;
            $cities->setCountry($this);
        }

        return $this;
    }

    /**
     * Remove cities
     *
     * @param \Cocorico\GeoBundle\Entity\City $cities
     */
    public function removeCity(City $cities)
    {
        $this->cities->removeElement($cities);
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


    public function __toString()
    {
        return $this->translate()->getName() . '';
    }
}

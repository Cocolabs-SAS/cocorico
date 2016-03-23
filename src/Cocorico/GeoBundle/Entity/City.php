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
 * City
 *
 * @ORM\Entity(repositoryClass="Cocorico\GeoBundle\Entity\CityRepository")
 *
 * @ORM\Table(name="geo_city")
 *
 */
class City
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
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Country", inversedBy="cities", cascade={"persist"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var country
     */
    private $country;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Area", inversedBy="cities", cascade={"persist"})
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var area
     */
    private $area;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\GeoBundle\Entity\Department", inversedBy="cities", cascade={"persist"})
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var department
     */
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\GeoBundle\Entity\Coordinate", mappedBy="city", cascade={"persist", "remove"})
     **/
    private $coordinates;

    public function __construct()
    {
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
     * Add coordinates
     *
     * @param  \Cocorico\GeoBundle\Entity\Coordinate $coordinates
     * @return Area
     */
    public function addCoordinate(Coordinate $coordinates)
    {
        if (!$this->coordinates->contains($coordinates)) {
            $this->coordinates[] = $coordinates;
            $coordinates->setCity($this);
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
     * Set area
     *
     * @param  \Cocorico\GeoBundle\Entity\Area $area
     * @return City
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
     * Set department
     *
     * @param  \Cocorico\GeoBundle\Entity\Department $department
     * @return City
     */
    public function setDepartment(Department $department)
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

    public function __toString()
    {
        return $this->translate()->getName() . '';
    }
}

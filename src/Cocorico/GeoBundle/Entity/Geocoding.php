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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Geocoding
 *
 * @ORM\Entity(repositoryClass="Cocorico\GeoBundle\Entity\GeocodingRepository")
 *
 * @ORM\Table(name="geo_geocoding")
 *
 */
class Geocoding
{
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
     * @var string
     *
     * @Assert\NotBlank(message="assert.not_blank")
     * @ORM\Column(name="viewport", type="string", length=100, nullable=false)
     */
    protected $viewport;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="assert.not_blank")
     * @ORM\Column(name="address_type", type="string", length=150, nullable=false)
     */
    protected $addressType;


    public function __construct()
    {

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
     * Set lat
     *
     * @param  string $lat
     * @return Geocoding
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
     * @return Geocoding
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
     * @return string
     */
    public function getViewport()
    {
        return $this->viewport;
    }

    /**
     * @param string $viewport
     */
    public function setViewport($viewport)
    {
        $this->viewport = $viewport;
    }

    /**
     * @return string
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * @param string $addressType
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;
    }

}

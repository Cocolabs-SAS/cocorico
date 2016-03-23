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
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CityTranslation
 *
 * @ORM\Table(name="geo_city_translation",indexes={
 *    @ORM\Index(name="name_gct_idx", columns={"name"})
 *  })
 *
 * @ORM\Entity
 */
class CityTranslation
{
    use ORMBehaviors\Translatable\Translation;
    use ORMBehaviors\Sluggable\Sluggable;


    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    protected $name;

    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getSluggableFields()
    {
        return ['name', 'id'];
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
     * Set name
     *
     * @param  string $name
     * @return Area
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}

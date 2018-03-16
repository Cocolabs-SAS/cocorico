<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ConfigBundle\Model;

use Cocorico\ConfigBundle\Entity\Parameter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseParameter
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseParameter
{

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\Column(type="string", length=50, name="name", nullable=false, unique = true)
     *
     * @var string $name
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, name="value", nullable=true)
     *
     * @var string $value
     */
    protected $value;

    /**
     * @ORM\Column(type="string", length=255, name="type", nullable=true)
     *
     * @var string $type
     */
    protected $type;

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Parameter
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Parameter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }



    public function __toString()
    {
        return (string)$this->getName();
    }
}

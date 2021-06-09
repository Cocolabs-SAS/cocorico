<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DirectoryImage
 *
 * @ORM\Table(name="directory_image", indexes={
 *    @ORM\Index(name="position_u_idx", columns={"position"})
 *  })
 * @ORM\Entity
 */
class DirectoryImage
{

    const IMAGE_DEFAULT = "default-user.png";
    const IMAGE_FOLDER = "/uploads/listings/images/";

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
     * @ORM\Column(type="string", length=255, name="name", nullable=false)
     *
     * @var string $name
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="smallint", nullable=false)
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Directory", inversedBy="images")
     * @ORM\JoinColumn(name="directory_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $directory;

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
     * Sets name.
     *
     * @param $name
     * @return $this
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

    /**
     * Set position
     *
     * @param  boolean $position
     * @return DirectoryImage
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return boolean
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set directory
     *
     * @param  Directory $directory
     * @return DirectoryImage
     */
    public function setDirectory(Directory $directory = null)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get listing
     *
     * @return Directory
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    public function getWebPath()
    {
        return null === $this->name ? null : self::IMAGE_FOLDER . $this->name;
    }

}

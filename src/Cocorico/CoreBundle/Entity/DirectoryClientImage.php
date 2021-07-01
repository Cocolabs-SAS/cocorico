<?php

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Model\BaseListingImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * DirectoryClientImage
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="directory_client_image", indexes={
 *    @ORM\Index(name="position_li_idx", columns={"position"})
 *  })
 *
 */
class DirectoryClientImage extends BaseListingImage
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
     * @ORM\ManyToOne(targetEntity="Directory", inversedBy="clientImages")
     * @ORM\JoinColumn(name="directory_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $directory;

    /**
     * @ORM\Column(name="description", type="string", nullable=true)
     * @var string
     */
    private $description;



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
     * Set directory
     *
     * @param  \Cocorico\CoreBundle\Entity\Directory $directory
     * @return DirectoryImage
     */
    public function setDirectory(Directory $directory = null)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory
     *
     * @return \Cocorico\CoreBundle\Entity\Directory
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return DirectoryClientImage
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}

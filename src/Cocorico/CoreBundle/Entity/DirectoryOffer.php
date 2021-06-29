<?php
namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DirectoryOffer
 *
 * @ORM\Entity
 * @ORM\Table(name="directory_offer",indexes={
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"})
 *  })
 *
 */
class DirectoryOffer
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Directory", inversedBy="offers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $directory;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return DirectoryOffer
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return DirectoryOffer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set directory.
     *
     * @param \Cocorico\CoreBundle\Entity\Directory|null $directory
     *
     * @return DirectoryOffer
     */
    public function setDirectory(\Cocorico\CoreBundle\Entity\Directory $directory = null)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory.
     *
     * @return \Cocorico\CoreBundle\Entity\Directory|null
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}

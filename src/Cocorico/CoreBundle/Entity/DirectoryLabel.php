<?php
namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DirectoryLabel
 *
 * @ORM\Entity
 * @ORM\Table(name="directory_label",indexes={
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"})
 *  })
 *
 */
class DirectoryLabel
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Directory", inversedBy="labels")
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
     * Set name.
     *
     * @param string $name
     *
     * @return DirectoryLabel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set directory.
     *
     * @param \Cocorico\CoreBundle\Entity\Directory|null $directory
     *
     * @return DirectoryLabel
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

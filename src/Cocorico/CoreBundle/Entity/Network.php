<?php
namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;
use BitMask\BitMask;
use BitMask\BitMaskInterface;

/**
 * Network
 *
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\NetworkRepository")
 *
 * @ORM\Table(name="network",indexes={
 *    @ORM\Index(name="siret", columns={"siret"}),
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"})
 *  })
 *
 */
class Network
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
     * @ORM\Column(name="brand", type="string", nullable=true)
     * @var string|null
     */
    private $brand;

    /**
     * @ORM\Column(name="accronym", type="string", nullable=true)
     * @var string|null
     */
    private $accronym;

    /**
     * @ORM\Column(name="website", type="string", nullable=true)
     * @var string|null
     */
    private $website;

    /**
     * @ORM\Column(name="siret", type="string", length=14, nullable=true)
     * @var string
     */
    private $siret;

    /**
     * @ORM\ManyToMany(targetEntity="Cocorico\CoreBundle\Entity\Directory", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var Structures[]
     */
    private $structures;


    public function __toString() {
        if ($this->brand) {
            return $this->brand;
        }
        return $this->name;
    }

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
     * @return Network
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
     * Set brand.
     *
     * @param string|null $brand
     *
     * @return Network
     */
    public function setBrand($brand = null)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand.
     *
     * @return string|null
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set accronym.
     *
     * @param string|null $accronym
     *
     * @return Network
     */
    public function setAccronym($accronym = null)
    {
        $this->accronym = $accronym;

        return $this;
    }

    /**
     * Get accronym.
     *
     * @return string|null
     */
    public function getAccronym()
    {
        return $this->accronym;
    }

    /**
     * Set website.
     *
     * @param string|null $website
     *
     * @return Network
     */
    public function setWebsite($website = null)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string|null
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set siret.
     *
     * @param string|null $siret
     *
     * @return Network
     */
    public function setSiret($siret = null)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get siret.
     *
     * @return string|null
     */
    public function getSiret()
    {
        return $this->siret;
    }
    /**
     * Add structure.
     *
     * @param \Cocorico\CoreBundle\Entity\Directory $structure
     *
     * @return Network
     */
    public function addStructure(\Cocorico\CoreBundle\Entity\Directory $structure)
    {
        $this->structures[] = $structure;
        $structure->addNetwork($this); // Directory owns

        return $this;
    }

    /**
     * Remove structure.
     *
     * @param \Cocorico\CoreBundle\Entity\Directory $structure
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeStructure(\Cocorico\CoreBundle\Entity\Directory $structure)
    {
        return $this->structures->removeElement($structure);
    }

    /**
     * Get structures.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStructures()
    {
        return $this->structures;
    }
}

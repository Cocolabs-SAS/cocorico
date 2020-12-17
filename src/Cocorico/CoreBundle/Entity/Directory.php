<?php
namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Directory
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\DirectoryRepository")
 *
 * @ORM\Table(name="directory",indexes={
 *    @ORM\Index(name="siret_idx", columns={"siret"}),
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"})
 *  })
 *
 */
class Directory
{
    use ORMBehaviors\Timestampable\Timestampable;

    const SECTOR_CHOICE = 'Choisir...';
    const SECTOR_RESTAURATION = 'Restauration';
    const SECTOR_CLEANING = 'Nettoyage, Propreté';
    const SECTOR_IT = 'Informatique';
    const SECTOR_INT = 'Prestation intellectuelle';
    const SECTOR_REPAIR = 'Dépannage, Réparation';
    const SECTOR_INDUSTRY = 'Sous-traitance industrielle';
    const SECTOR_TEXTILE = 'Textiles';
    const SECTOR_DECO = 'Ameublement, décoration';
    const SECTOR_LOGISTICS = 'Logistique, livraisons';
    const SECTOR_MISC = 'Other';

    public static $sectorValues = array(
        self::SECTOR_CHOICE,
        self::SECTOR_RESTAURATION,
        self::SECTOR_CLEANING,
        self::SECTOR_IT,
        self::SECTOR_INT,
        self::SECTOR_REPAIR,
        self::SECTOR_INDUSTRY,
        self::SECTOR_TEXTILE,
        self::SECTOR_DECO,
        self::SECTOR_LOGISTICS,
        self::SECTOR_MISC,
    );

    const PRESTA_CHOICE = 'Choisir...';
    const PRESTA_DISP = 'Mise à disposition';
    const PRESTA_PREST = 'Prestation et/ou vente de biens';
    public static $prestaTypeValues = array(
        self::PRESTA_CHOICE,
        self::PRESTA_DISP,
        self::PRESTA_PREST,
    );

    const STRUCT_CHOICE = 'Choisir...';
    const STRUCT_EI = 'EI';
    const STRUCT_ETTI = 'ETTI';
    const STRUCT_ACI = 'ACI';
    const STRUCT_AI = 'AI';
    public static $kindValues = array(
        self::STRUCT_CHOICE,
        self::STRUCT_EI,
        self::STRUCT_ETTI,
        self::STRUCT_ACI,
        self::STRUCT_AI,
    );

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     * @var integer
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="siret", type="string", length=14, nullable=true)
     * @var string
     */
    private $siret;

    /**
     * @var string|null
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="kind", type="string", nullable=false)
     */
    private $kind;

    /**
     * @ORM\Column(name="website", type="string", nullable=true)
     * @var string|null
     */
    private $website;

    /**
     * @ORM\Column(name="city", type="string", nullable=true)
     * @var string|null
     */
    private $city;

    /**
     * @ORM\Column(name="post_code", type="string", nullable=true)
     * @var string|null
     */
    private $postCode;

    /**
     * @ORM\Column(name="department", type="integer", nullable=true)
     * @var string|null
     */
    private $department;

    /**
     * @ORM\Column(name="region", type="string", nullable=true)
     * @var string|null
     */
    private $region;

    /**
     * @ORM\Column(name="longitude", type="decimal", nullable=true)
     * @var string|null
     */
    private $longitude;

    /**
     * @ORM\Column(name="latitude", type="decimal", nullable=true)
     * @var string|null
     */
    private $latitude;

    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     * @var string|null
     */
    private $phone;

    /**
     * @ORM\Column(name="presta_type", type="string", nullable=true)
     * @var string|null
     */
    private $prestaType;

    /**
     * @ORM\Column(name="sector", type="string", nullable=true)
     * @var string|null
     */
    private $sector;

    /**
     * @ORM\Column(name="naf", type="string", length=5, nullable=true)
     * @var string|null
     */
    private $naf;

    /**
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     * @var bool
     */
    private $isActive;

    /**
     * @ORM\Column(name="brand", type="string", nullable=true)
     * @var string|null
     */
    private $brand;


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
     * @return Directory
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
     * Set siret.
     *
     * @param string $siret
     *
     * @return Directory
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get siret.
     *
     * @return string
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * Set email.
     *
     * @param string|null $email
     *
     * @return Directory
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set kind.
     *
     * @param string $kind
     *
     * @return Directory
     */
    public function setKind($kind)
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * Get kind.
     *
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Set website.
     *
     * @param string|null $website
     *
     * @return Directory
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
     * Set city.
     *
     * @param string|null $city
     *
     * @return Directory
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set postCode.
     *
     * @param string|null $postCode
     *
     * @return Directory
     */
    public function setPostCode($postCode = null)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode.
     *
     * @return string|null
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set department.
     *
     * @param string|null $department
     *
     * @return Directory
     */
    public function setDepartment($department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department.
     *
     * @return string|null
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set region.
     *
     * @param string|null $region
     *
     * @return Directory
     */
    public function setRegion($region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region.
     *
     * @return string|null
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set longitude.
     *
     * @param string|null $longitude
     *
     * @return Directory
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude.
     *
     * @param string|null $latitude
     *
     * @return Directory
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set phone.
     *
     * @param string|null $phone
     *
     * @return Directory
     */
    public function setPhone($phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set prestaType.
     *
     * @param string|null $prestaType
     *
     * @return Directory
     */
    public function setPrestaType($prestaType = null)
    {
        $this->prestaType = $prestaType;

        return $this;
    }

    /**
     * Get prestaType.
     *
     * @return string|null
     */
    public function getPrestaType()
    {
        return $this->prestaType;
    }

    /**
     * Set sector.
     *
     * @param string|null $sector
     *
     * @return Directory
     */
    public function setSector($sector = null)
    {
        $this->sector = $sector;

        return $this;
    }

    /**
     * Get sector.
     *
     * @return string|null
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * Set naf.
     *
     * @param string|null $naf
     *
     * @return Directory
     */
    public function setNaf($naf = null)
    {
        $this->naf = $naf;

        return $this;
    }

    /**
     * Get naf.
     *
     * @return string|null
     */
    public function getNaf()
    {
        return $this->naf;
    }

    /**
     * Set isActive.
     *
     * @param bool $isActive
     *
     * @return Directory
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set brand.
     *
     * @param string|null $brand
     *
     * @return Directory
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
}
?>

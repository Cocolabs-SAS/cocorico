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
 *    @ORM\Index(name="nature_idx", columns={"nature"}),
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"})
 *  })
 *
 */
class Directory
{
    use ORMBehaviors\Timestampable\Timestampable;

    public static $sectorValues = [
    'Choisir...',
    'Agro-Alimentaire',
    'Bâtiments et Travaux publics',
    'Entretien du linge',
    'Collecte & Traitement des déchets',
    'Communication, Marketing',
    'Assistance générale et administrative',
    'Restauration',
    'Hygiène et Propreté',
    'Prestations informatiques',
    'Prestations intellectuelles',
    'Réparations & Dépannages',
    'Sous-traitance industrielle',
    'Textiles',
    'Ameublement, déco, textile & Artisanat',
    'Déménagements, Livraisons & Transports',
    'Création et entretien des espaces verts',
    'Entretien et maintenance de l\'espace urbain',
    'Mailing, archivage, secrétariat',
    'Médiation urbaine',
    'Ventes de livres et ouvrages',
    'Location de Véhicules & Vélos',
    'Recyclage, économie circulaire',
    'Agro-alimentaire - Autre',
    'Pêche/Pisciculture',
    'Transport de personnes',
    'Services à la personne',
    'Restauration de livres',
    'Autre'
    ];

    /*
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
    */

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
    const STRUCT_EA = 'EA';
    const STRUCT_ETTI = 'ETTI';
    const STRUCT_EITI = 'EITI';
    const STRUCT_EATT = 'EATT';
    const STRUCT_AI = 'AI';
    const STRUCT_ACI = 'ACI';
    const STRUCT_GEIQ = 'GEIQ';
    public static $kindValues = array(
        self::STRUCT_CHOICE,
        self::STRUCT_EI,
        self::STRUCT_EA,
        self::STRUCT_ETTI,
        self::STRUCT_EITI,
        self::STRUCT_EATT,
        self::STRUCT_ACI,
        self::STRUCT_AI,
        self::STRUCT_GEIQ,
    );

    public static $exportColumns = array(
        'name' => 'Raison sociale',
        'siret' => 'Siret',
        'kind' => 'Structure',
        'sector' => 'Secteur',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'website' => 'Site web',
        'city' => 'Ville',
        'department' => 'Département',
        'region' => 'Région',
        'postCode' => 'Code postal',
    );

    public static $regions = array(
        "Toutes",
        "Auvergne-Rhône-Alpes",
        "Bourgogne-Franche-Comté",
        "Bretagne",
        "Centre-Val de Loire",
        "Corse",
        "Grand Est",
        "Guadeloupe",
        "Guyane",
        "Hauts-de-France",
        "Île-de-France",
        "La Réunion",
        "Martinique",
        "Mayotte",
        "Normandie",
        "Nouvelle-Aquitaine",
        "Occitanie",
        "Pays de la Loire",
        "Provence-Alpes-Côte d'Azur",
        "Collectivités d'outre-mer",
        "Anciens territoires d'outre-mer",
    );

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(name="c1_id", type="integer", nullable=true)
     * @var integer|null
     * C1 Identifier (for synchronisation)
     */
    private $c1Id;

    /**
     * @ORM\Column(name="c4_id", type="integer", nullable=true)
     * @var integer|null
     * C4 Identifier (for synchronisation)
     */
    private $c4Id;

    /**
     * @ORM\Column(name="is_delisted", type="boolean", nullable=true)
     * @var bool
     * Delisted indicator (if not active on C1)
     */
    private $isDelisted;

    /**
     * @ORM\Column(name="c1_source", type="string", nullable=true)
     * @var string|null
     * C1 Source field
     */
    private $c1Source;

    /**
     * @ORM\Column(name="last_sync_date", type="datetime", nullable=true)
     * @var datetime|null
     * Last c1 sync time
     */
    private $lastSyncDate;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="nature", type="string", nullable=true)
     * @var string|null
     */
    private $nature;

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
     * @ORM\Column(name="department", type="string", nullable=true)
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
     * @ORM\Column(name="sector", type="text", nullable=true)
     * @var string|null
     */
    private $sector;

    /**
     * @ORM\Column(name="naf", type="string", length=5, nullable=true)
     * @var string|null
     */
    private $naf;

    /**
     * @ORM\Column(name="is_active", type="string", nullable=true)
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


    /**
     * Set c1Id.
     *
     * @param int|null $c1Id
     *
     * @return Directory
     */
    public function setC1Id($c1Id = null)
    {
        $this->c1Id = $c1Id;

        return $this;
    }

    /**
     * Get c1Id.
     *
     * @return int|null
     */
    public function getC1Id()
    {
        return $this->c1Id;
    }

    /**
     * Set c4Id.
     *
     * @param int|null $c4Id
     *
     * @return Directory
     */
    public function setC4Id($c4Id = null)
    {
        $this->c4Id = $c4Id;

        return $this;
    }

    /**
     * Get c4Id.
     *
     * @return int|null
     */
    public function getC4Id()
    {
        return $this->c4Id;
    }

    /**
     * Set isDelisted.
     *
     * @param bool|null $isDelisted
     *
     * @return Directory
     */
    public function setIsDelisted($isDelisted = null)
    {
        $this->isDelisted = $isDelisted;

        return $this;
    }

    /**
     * Get isDelisted.
     *
     * @return bool|null
     */
    public function getIsDelisted()
    {
        return $this->isDelisted;
    }

    /**
     * Set c1Source.
     *
     * @param string|null $c1Source
     *
     * @return Directory
     */
    public function setC1Source($c1Source = null)
    {
        $this->c1Source = $c1Source;

        return $this;
    }

    /**
     * Get c1Source.
     *
     * @return string|null
     */
    public function getC1Source()
    {
        return $this->c1Source;
    }

    /**
     * Set lastSyncDate.
     *
     * @param \DateTime|null $lastSyncDate
     *
     * @return Directory
     */
    public function setLastSyncDate($lastSyncDate = null)
    {
        $this->lastSyncDate = $lastSyncDate;

        return $this;
    }

    /**
     * Get lastSyncDate.
     *
     * @return \DateTime|null
     */
    public function getLastSyncDate()
    {
        return $this->lastSyncDate;
    }

    /**
     * Set nature.
     *
     * @param string|null $nature
     *
     * @return Directory
     */
    public function setNature($nature = null)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * Get nature.
     *
     * @return string|null
     */
    public function getNature()
    {
        return $this->nature;
    }
}

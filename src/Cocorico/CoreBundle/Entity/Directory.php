<?php
namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;
use BitMask\BitMask;
use BitMask\BitMaskInterface;

/**
 * Directory
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\DirectoryRepository")
 *
 * @ORM\Table(name="directory",indexes={
 *    @ORM\Index(name="siret_idx", columns={"siret"}),
 *    @ORM\Index(name="nature_idx", columns={"nature"}),
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"}),
 *    @ORM\Index(name="range_idx", columns={"geo_range"}),
 *    @ORM\Index(name="polrange_idx", columns={"pol_range"}),
 *    @ORM\Index(name="presta_type_idx", columns={"presta_type"}),
 *    @ORM\Index(name="siret_is_valid_idx", columns={"siret_is_valid"}),
 *    @ORM\Index(name="kind_idx", columns={"kind"}),
 *    @ORM\Index(name="name_idx", columns={"name"}),
 *    @ORM\Index(name="region_idx", columns={"region"}),
 *    @ORM\Index(name="post_code_idx", columns={"post_code"}),
 *    @ORM\Index(name="is_active_idx", columns={"is_active"}),
 *    @ORM\Index(name="is_delisted_idx", columns={"is_delisted"}),
 *    @ORM\Index(name="latitude_idx", columns={"latitude"}),
 *    @ORM\Index(name="is_qpv_idx", columns={"is_qpv"}),
 *    @ORM\Index(name="some_list_idx", columns={"nature","is_delisted", "latitude"})
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

    # const PRESTA_CHOICE = 'Choisir...';
    # const PRESTA_DISP = 'Mise à disposition';
    # const PRESTA_PREST = 'Prestation et/ou vente de biens';

    const PRESTA_CHOICE = 1 << 0; # 1
    const PRESTA_DISP = 1 << 1; # 2
    const PRESTA_PREST = 1 << 2; # 4
    const PRESTA_BUILD = 1 << 3; #8

    public static $prestaTypeValues = array(
        self::PRESTA_CHOICE => 'Choisir ...',
        self::PRESTA_DISP => 'Mise à disposition - Interim',
        self::PRESTA_PREST => 'Prestation de service',
        self::PRESTA_BUILD => 'Fabrication et commercialisation de biens',
    );

    # public static $prestaTypeValues = array(
    #     'choice' => self::PRESTA_CHOICE,
    #     'disp' => self::PRESTA_DISP,
    #     'prest' => self::PRESTA_PREST,
    #     'build' => self::PRESTA_BUILD,
    # );


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

    public static $kindFullString = array (
        self::STRUCT_CHOICE => self::STRUCT_CHOICE,
        self::STRUCT_EI => 'Entreprise d\'insertion (EI)',
        self::STRUCT_EA => 'Entreprise adaptée (EA)',
        self::STRUCT_EITI => 'Entreprise d\'insertion par le travail indépendant (EITI)',
        self::STRUCT_ETTI => 'Entreprise de travail temporaire d\'insertion (ETTI)',
        self::STRUCT_EATT => 'Enterprises adaptées de travail temporaire (EATT)',
        self::STRUCT_ACI => 'Atelier et chantier d\'insertion (ACI)',
        self::STRUCT_AI => 'Assocation intermédiaire (AI)',
        self::STRUCT_GEIQ => 'Groupement d\'employeurs pour l\'insertion et la qualification (GEIQ)',
    );

    const natureValues = array(
        'siege' => 'Conventionné avec la Direccte',
        'antenne' => 'Rattaché à un autre conventionnement'
    );

    /* Political ranges */
    const PR_OTHER = 0;
    const PR_DEPARTEMENT = 1;
    const PR_REGION = 2;
    const PR_FRANCE = 3;

    public static $polRangeValues = array (
        self::PR_OTHER => 'entity.directory.polrange.other',
        self::PR_DEPARTEMENT => 'entity.directory.polrange.departement',
        self::PR_REGION => 'entity.directory.polrange.region',
        self::PR_FRANCE => 'entity.directory.polrange.france',
    );



    public static $exportColumns = array(
        'name' => 'Raison sociale',
        'brand' => 'Enseigne',
        # 'siret' => 'Siret',
        'getNiceSiret' => 'Siret',
        'natureText' => 'Établissement',
        'kind' => 'Type',
        'sectorString' => 'Secteur',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'website' => 'Site web',
        'city' => 'Ville',
        'department' => 'Département',
        'region' => 'Région',
        'postCode' => 'Code postal',
        'getONQpv' => 'En zone QPV',
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

    public function __toString() {
        return $this->name;
    }

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
     * @ORM\Column(name="sync_skip", type="boolean", nullable=true)
     * @var bool
     * Skip during synchronisation
     */
    private $syncSkip;

    /**
     * @ORM\Column(name="is_consortium", type="boolean", nullable=true)
     * @var bool
     * Is used as a "consortium" (group of structures)
     * (C4 specific structure)
     */
    private $isConsortium;

    /**
     * @ORM\Column(name="siret_is_valid", type="boolean", nullable=true)
     * @var bool
     * Whether siret is valid or not
     */
    private $siretIsValid;

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
     * @ORM\Column(name="longitude", type="decimal", scale=6, nullable=true)
     * @var string|null
     */
    private $longitude;

    /**
     * @ORM\Column(name="latitude", type="decimal", scale=6, nullable=true)
     * @var string|null
     */
    private $latitude;

    /**
     * @ORM\Column(name="phone", type="string", nullable=true)
     * @var string|null
     */
    private $phone;

    /**
    * @ORM\Column(name="presta_type", type="bitmask", nullable=true)
    * @var \Doctrine\DBAL\Types\Type\bitmask
    */
    private $prestaType = BitMaskType::class;

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
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     * @var bool
     */
    private $isActive;

    /**
     * @ORM\Column(name="is_first_page", type="boolean", nullable=true, options={"default":"0"})
     * @var bool
     */
    private $isFirstPage;

    /**
     * @ORM\Column(name="is_cocontracting", type="boolean", nullable=true)
     * @var bool
     */
    private $isCoContracting;

    /**
     * @ORM\Column(name="brand", type="string", nullable=true)
     * @var string|null
     */
    private $brand;

    /**
     * @ORM\Column(name="ig_employees", type="string", nullable=true)
     * @var string|null
     */
    private $employees;

    /**
     * @ORM\Column(name="ig_ca", type="integer", nullable=true)
     * @var integer|null
     */
    private $chiffreAffaire;
    /**
     * @ORM\Column(name="ig_date_constitution", type="datetime", nullable=true)
     * @var datetime|null
     */
    private $dateConstitution;

    /**
     * @var string|null
     * @ORM\Column(name="admin_email", type="string", nullable=true)
     */
    private $adminEmail;

    /**
     * @var string|null
     * @ORM\Column(name="admin_name", type="string", nullable=true)
     */
    private $adminName;

    /**
     * @ORM\OneToMany(targetEntity="DirectoryListingCategory", mappedBy="directory", cascade={"persist", "remove"}, orphanRemoval=true)//, fetch="EAGER"
     *
     */
    private $directoryListingCategories;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="structures", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var Users
     */
    private $users;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Cocorico\CoreBundle\Entity\Listing", inversedBy="structures", cascade={"persist"})
     * @ORM\JoinColumn(name="directory_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var Listings
     */
    private $listings;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Cocorico\CoreBundle\Entity\Network", inversedBy="structures", cascade={"persist"})
     * @ORM\JoinColumn(name="network_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var Networks
     */
    private $networks;

    /**
     * @ORM\Column(name="geo_range", type="integer", nullable=true)
     *
     * @var integer|null
     */
    private $range;

    /**
     * @ORM\Column(name="pol_range", type="integer", nullable=true)
     *
     * @var integer|null
     */
    private $polRange;

    /**
     *
     * @ORM\OneToMany(targetEntity="DirectoryImage", mappedBy="directory", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "asc"})
     *
     * @var DirectoryImage[]
     */
    protected $images;

    /**
     *
     * @ORM\OneToMany(targetEntity="DirectoryClientImage", mappedBy="directory", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $clientImages;

    /**
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="qpv_code", type="text", length=16, nullable=true)
     *
     * @var string
     */
    private $qpvCode;

    /**
     * @ORM\Column(name="qpv_name", type="text", length=256, nullable=true)
     *
     * @var string
     */
    private $qpvName;

    /**
     * @ORM\Column(name="is_qpv", type="boolean", nullable=true)
     * @var bool
     */
    private $isQpv;

    /**
     * @ORM\OneToMany(targetEntity="DirectoryLabel", mappedBy="directory", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $labels;

    /**
     * @ORM\OneToMany(targetEntity="DirectoryOffer", mappedBy="directory", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $offers;


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

    public function getNameOrBrand()
    {
        if ($this->brand) {
            return $this->brand;
        }
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
     * Get siren.
     *
     * @return string
     */
    public function getSiren()
    {
        if ($this->siret) {
            return substr($this->siret,0, 9);
        }
        return '';
    }

    public function getNiceSiret()
    {
        $s = $this->siret;
        $srn =  substr($s,0,3) . " " . substr($s,3,3) . " " . substr($s,6,3);
        if (! $this->getSiretIsValid())
        #if (strlen($s) == 9)
            return $srn;
        return $srn . " " . substr($s,9,5);
    }

    /**
     * Get Sector String
     *
     * @return string
     */
    public function getSectorString($max=1000, $separator=" - ")
    {
        $out = [];
        $cats = $this->getDirectoryListingCategories(); 

        foreach ($cats as $cat) {
            $myname = $cat->getCategory()->getName();
            if ($myname == 'Autre') {
                $myname = $cat->getCategory()->getParent()->getName();
            }
            if ($myname == 'Autres' || $myname == 'Other' ){
                continue;
            }
            $out[] = $myname;
            if (count($out) > $max) {
                $out[] = '...';
                break;
            }
        }

        return implode($separator, $out);
    }

    /**
     * Get Valid Siret.
     *
     * @return string
     */
    public function getValidSiret()
    {
        if ($this->siret and $this->siretIsValid) {
            return $this->siret;
        }
        return '';
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

    public function getKindString()
    {
        return  self::$kindFullString[$this->kind];
    
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
     * Set isFirstPage.
     *
     * @param bool $isFirstPage
     *
     * @return Directory
     */
    public function setIsFirstPage($isFirstPage)
    {
        $this->isFirstPage = $isFirstPage;

        return $this;
    }

    /**
     * Get isFirstPage.
     *
     * @return bool
     */
    public function getIsFirstPage()
    {
        return $this->isFirstPage;
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
     * Set SyncSkip.
     *
     * @param bool|null $syncSkip
     *
     * @return Directory
     */
    public function setSyncSkip($syncSkip = null)
    {
        $this->syncSkip = $syncSkip;

        return $this;
    }

    /**
     * Get SynSkip.
     *
     * @return bool|null
     */
    public function getSyncSkip()
    {
        return $this->syncSkip;
    }

    /**
     * Set isConsortium.
     *
     * @param bool|null $isConsortium
     *
     * @return Directory
     */
    public function setIsConsortium($isConsortium = null)
    {
        $this->isConsortium = $isConsortium;

        return $this;
    }

    /**
     * Get isConsortium.
     *
     * @return bool|null
     */
    public function getIsConsortium()
    {
        return $this->isConsortium;
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
    
    /**
     * Get natureText.
     *
     * @return string|null
     */
    public function getNatureText()
    {
        return self::natureValues[$this->nature];
    }

    /**
     * Set siretIsValid.
     *
     * @param bool|null $siretIsValid
     *
     * @return Directory
     */
    public function setSiretIsValid($siretIsValid = null)
    {
        $this->siretIsValid = $siretIsValid;

        return $this;
    }

    /**
     * Get siretIsValid.
     *
     * @return bool|null
     */
    public function getSiretIsValid()
    {
        if (str_ends_with($this->siret, '99999')) {
            return false;
        }
        return $this->siretIsValid != 0 &&
                $this->siretIsValid != false &&
                $this->siretIsValid != null;
    }

    /**
     * Set employees.
     *
     * @param int|null $employees
     *
     * @return Directory
     */
    public function setEmployees($employees = null)
    {
        $this->employees = $employees;

        return $this;
    }

    /**
     * Get employees.
     *
     * @return int|null
     */
    public function getEmployees()
    {
        return $this->employees;
    }

    /**
     * Set chiffreAffaire.
     *
     * @param int|null $chiffreAffaire
     *
     * @return Directory
     */
    public function setChiffreAffaire($chiffreAffaire = null)
    {
        $this->chiffreAffaire = $chiffreAffaire;

        return $this;
    }

    /**
     * Get chiffreAffaire.
     *
     * @return int|null
     */
    public function getChiffreAffaire()
    {
        return $this->chiffreAffaire;
    }

    /**
     * Set dateConstitution.
     *
     * @param \DateTime|null $dateConstitution
     *
     * @return Directory
     */
    public function setDateConstitution($dateConstitution = null)
    {
        $this->dateConstitution = $dateConstitution;

        return $this;
    }

    /**
     * Get dateConstitution.
     *
     * @return \DateTime|null
     */
    public function getDateConstitution()
    {
        return $this->dateConstitution;
    }


    /**
     * Set adminEmail.
     *
     * @param string|null $adminEmail
     *
     * @return Directory
     */
    public function setAdminEmail($adminEmail = null)
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    /**
     * Get adminEmail.
     *
     * @return string|null
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * Set adminName.
     *
     * @param string|null $adminName
     *
     * @return Directory
     */
    public function setAdminName($adminName = null)
    {
        $this->adminName = $adminName;

        return $this;
    }

    /**
     * Get adminName.
     *
     * @return string|null
     */
    public function getAdminName()
    {
        return $this->adminName;
    }

    /**
     * Add category
     *
     * @param  \Cocorico\CoreBundle\Entity\DirectoryListingCategory $directoryListingCategory
     * @return Directory
     */
    public function addDirectoryListingCategory(DirectoryListingCategory $directoryListingCategory)
    {
        $directoryListingCategory->setDirectory($this);
        $this->directoryListingCategories[] = $directoryListingCategory;

        return $this;
    }

    public function hasDirectoryListingCategory()
    {
        return count($this->directoryListingCategories) > 0;
    }

    /**
     * Remove category
     *
     * @param \Cocorico\CoreBundle\Entity\DirectoryListingCategory $directoryListingCategory
     */
    public function removeDirectoryListingCategory(DirectoryListingCategory $directoryListingCategory)
    {
//        foreach ($listingListingCategory->getValues() as $value) {
//            $listingListingCategory->removeValue($value);
//        }

        $this->directoryListingCategories->removeElement($directoryListingCategory);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection|DirectoryListingCategory[]
     */
    public function getDirectoryListingCategories()
    {
        return $this->directoryListingCategories;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->directoryListingCategories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->networks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->offers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->labels = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->clientImages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user
     *
     * @param \Cocorico\UserBundle\Entity\User $user
     *
     * @return Directory
     */
    public function addUser(\Cocorico\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;
        // $user->addStructure($this); // It's the user

        return $this;
    }

    /**
     * Remove user.
     *
     * @param \Cocorico\UserBundle\Entity\User $user
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUser(\Cocorico\UserBundle\Entity\User $user)
    {
        return $this->users->removeElement($user);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function getFirstUser()
    {
        return $this->users[0];
    }

    public function hasUser($user)
    {
        return $this->users->contains($user);
    }

    public function hasUsers()
    {
        return count($this->users);
    }

    /**
     * Add listing
     *
     * @param \Cocorico\CoreBundle\Entity\Listing $listing
     *
     * @return Directory
     */
    public function addListing(\Cocorico\CoreBundle\Entity\Listing $listing)
    {
        $this->listings[] = $listing;
        // $listing->addStructure($this); // It's the listing

        return $this;
    }

    /**
     * Remove listing.
     *
     * @param \Cocorico\CoreBundle\Entity\Listing $listing
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeListing(\Cocorico\CoreBundle\Entity\Listing $listing)
    {
        return $this->listings->removeElement($listing);
    }

    /**
     * Get listings.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListings()
    {
        return $this->listings;
    }

    public function getFirstListing()
    {
        return $this->listings[0];
    }

    public function hasListing($listing)
    {
        return $this->listings->contains($listing);
    }

    public function hasListings()
    {
        return count($this->listings);
    }

    /**
     * Add network
     *
     * @param \Cocorico\CoreBundle\Entity\Network $network
     *
     * @return Directory
     */
    public function addNetwork(\Cocorico\CoreBundle\Entity\Network $network)
    {
        $this->networks[] = $network;
        // $network->addStructure($this); // It's the network

        return $this;
    }

    /**
     * Remove network.
     *
     * @param \Cocorico\CoreBundle\Entity\Network $network
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNetwork(\Cocorico\CoreBundle\Entity\Network $network)
    {
        return $this->networks->removeElement($network);
    }

    /**
     * Get networks.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNetworks()
    {
        return $this->networks;
    }

    public function getFirstNetwork()
    {
        return $this->networks[0];
    }

    public function hasNetwork($network)
    {
        return $this->networks->contains($network);
    }

    public function hasNetworks()
    {
        return count($this->networks);
    }


    /**
     * Set range
     *
     * @param  integer $range
     * @return $this
     */
    public function setRange($range)
    {
        $this->range = $range;

        return $this;
    }

    /**
     * Get range
     *
     * @return integer
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Set political range
     *
     * @param  integer $polRange
     * @return $this
     */
    public function setPolRange($polRange)
    {
        if (!in_array($polRange, array_keys(self::$polRangeValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.prestaType : %s.', $polRange)
            );
        }
        $this->polRange = $polRange;

        return $this;
    }

    /**
     * Get political range
     *
     * @return integer
     */
    public function getPolRange()
    {
        return $this->polRange;
    }


    /**
     * Get Political range Text
     *
     * @return string
     */
    public function getPolRangeText()
    {
        return self::$polRangeValues[$this->getPolRange()];
    }

    public function getNiceRange()
    {
        switch ($this->getPolRange()) {
            case 3:
                return 'France entière';
            case 2:
                return 'région ('. $this->getRegion() .')';
            case 1:
                return 'département ('. $this->getDepartment() .')';
            default:
                if ($this->getRange() != null) {
                    return $this->getRange() . " km";
                } else {
                    return 'non disponible';
                }
        }
    }


    /**
     * Add images.
     *
     * @param DirectoryImage $image
     *
     * @return $this
     */
    public function addImage(DirectoryImage $image)
    {
        $image->setDirectory($this); //Because the owning side of this relation is user image
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove images.
     *
     * @param DirectoryImage $image
     */
    public function removeImage(DirectoryImage $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images.
     *
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }


    /**
     * Add client images
     *
     * @param  \Cocorico\CoreBundle\Entity\DirectoryClientImage $image
     * @return Directory
     */
    public function addClientImage(DirectoryClientImage $image)
    {
        $image->setDirectory($this); //Because the owning side of this relation is image
        $this->clientImages[] = $image;

        return $this;
    }

    /**
     * Remove client images
     *
     * @param \Cocorico\CoreBundle\Entity\DirectoryClientImage $image
     */
    public function removeClientImage(DirectoryClientImage $image)
    {
        $this->clientImages->removeElement($image);
        $image->setDirectory(null);
    }

    /**
     * Get client images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClientImages()
    {
        return $this->clientImages;
    }

    /**
     * Set description
     *
     * @param  string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set qpvCode
     *
     * @param  string $qpvCode
     * @return $this
     */
    public function setQpvCode($qpvCode)
    {
        $this->qpvCode = $qpvCode;

        return $this;
    }

    /**
     * Get qpvCode
     *
     * @return string
     */
    public function getQpvCode()
    {
        return $this->qpvCode;
    }

    /**
     * Set qpvName
     *
     * @param  string $qpvName
     * @return $this
     */
    public function setQpvName($qpvName)
    {
        $this->qpvName = $qpvName;

        return $this;
    }

    /**
     * Get qpvName
     *
     * @return string
     */
    public function getQpvName()
    {
        return $this->qpvName;
    }

    /**
     * Set isQpv.
     *
     * @param bool $isQpv
     *
     * @return Directory
     */
    public function setIsQpv($isQpv)
    {
        $this->isQpv = $isQpv;

        return $this;
    }

    /**
     * Get isQpv.
     *
     * @return bool
     */
    public function getIsQpv()
    {
        return $this->isQpv;
    }
 
    /**
     * Get nice.
     *
     * @return bool
     */
    public function getONQpv()
    {
        return  $this->isQpv ? 'Oui' : 'Non';
    } 

    /*
     * Presta Type Logic (bitmask type)
     */

    public function getPrestaType()
    {
        if (is_string($this->prestaType))
            {
            return new Bitmask();
            }

        return $this->prestaType;
    }

    /**
     * Force convert prestaType data type to int
     *
     */
    public function prestaTypeToInt() 
    {
        if (is_string($this->prestaType)) 
        {
            $this->prestaType = 0;
        } else {
            $this->prestaType = $this->prestaType->get();
        }
    }

    /**
     * Set PrestaType
     *
     * @return self
     */
    public function setPrestaType(BitMaskInterface $prestaType) : self
    {
        $this->prestaType = $prestaType;

        return $this;
    }

    /**
     * Enable Single PrestaType
     *
     * @return self
     */
    public function enablePrestaType($prestaType) : self
    {

        $pt = $this->getPrestaType();
        $pt->setBit($prestaType);
        $this->prestaType = $pt;
        return $this;
    }
    /**
     * Disable Single PrestaType
     *
     * @return self
     */
    public function disablePrestaType($prestaType) : self
    {
        $pt = $this->getPrestaType();
        $pt->unsetBit($prestaType);
        $this->prestaType = $pt;
        return $this;
    }
    public function hasPrestaType() : bool
    {
        return $this->isPrestaTypeChoice()
            || $this->isPrestaTypeDisp()
            || $this->isPrestaTypePrest();
    }
    /**
     * Check prestaType choice
     *
     * @return bool
     */
    public function isPrestaTypeChoice() : bool
    {
        return $this->getPrestaType()->isSetBit(static::PRESTA_CHOICE);
    }

    /*
     * Check prestaType disposition
     *
     * @return bool
     */
    public function isPrestaTypeDisp() : bool
    {
        return $this->getPrestaType()->isSetBit(static::PRESTA_DISP);
    }

    /**
     * Check prestaType prestation
     *
     * @return bool
     */
    public function isPrestaTypePrest() : bool
    {
        return $this->getPrestaType()->isSetBit(static::PRESTA_PREST);
    }

    /**
     * Check prestaType build
     *
     * @return bool
     */
    public function isPrestaTypeBuild() : bool
    {
        return $this->getPrestaType()->isSetBit(static::PRESTA_BUILD);
    }

    /**
     * Set prestaType choice
     *
     * @param bool $set
     *
     * @return self
     */
    public function setPrestaTypeChoice($set) : self
    {
        return $set
            ? $this->enablePrestaType(self::PRESTA_CHOICE)
            : $this->disablePrestaType(self::PRESTA_CHOICE);
    }
    public function getPrestaTypeChoice() : bool {
        return $this->isPrestaTypeChoice();
    }

    /**
     * Check prestaType Disposition
     *
     * @param bool $set
     *
     * @return self
     */
    public function setPrestaTypeDisp($set) : self
    {
        return $set
            ? $this->enablePrestaType(self::PRESTA_DISP)
            : $this->disablePrestaType(self::PRESTA_DISP);
    }
    public function getPrestaTypeDisp() : bool {
        return $this->isPrestaTypeDisp();
    }

    /**
     * Check prestaType Prestation
     *
     * @param bool $set
     *
     * @return self
     */
    public function setPrestaTypePrest($set) : self
    {
        return $set
            ? $this->enablePrestaType(self::PRESTA_PREST)
            : $this->disablePrestaType(self::PRESTA_PREST);
    }
    public function getPrestaTypePrest() : bool {
        return $this->isPrestaTypePrest();
    }
    /**
     * Check prestaType Build
     *
     * @param bool $set
     *
     * @return self
     */
    public function setPrestaTypeBuild($set) : self
    {
        return $set
            ? $this->enablePrestaType(self::PRESTA_BUILD)
            : $this->disablePrestaType(self::PRESTA_BUILD);
    }
    public function getPrestaTypeBuild() : bool {
        return $this->isPrestaTypeBuild();
    }

    public function prestaTypeText($separator=' , ')
    {
        $ret = array();
        if ($this->isPrestaTypeDisp()) {
            array_push($ret, self::$prestaTypeValues[self::PRESTA_DISP]);
        }
        if ($this->isPrestaTypePrest()) {
            array_push($ret, self::$prestaTypeValues[self::PRESTA_PREST]);
        }
        if ($this->isPrestaTypeBuild()) {
            array_push($ret, self::$prestaTypeValues[self::PRESTA_BUILD]);
        }

        return implode($separator, $ret);
    }


    /**
     * Add label.
     *
     * @param \Cocorico\CoreBundle\Entity\DirectoryLabel $label
     *
     * @return Directory
     */
    public function addLabel(\Cocorico\CoreBundle\Entity\DirectoryLabel $label)
    {
        if (!$this->labels->contains($label)) {
            $label->setDirectory($this);
            $this->labels->add($label);
        }

        return $this;
    }

    /**
     * Remove label.
     *
     * @param \Cocorico\CoreBundle\Entity\DirectoryLabel $label
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLabel(\Cocorico\CoreBundle\Entity\DirectoryLabel $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * Get labels.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Add offer.
     *
     * @param \Cocorico\CoreBundle\Entity\DirectoryOffer $offer
     *
     * @return Directory
     */
    public function addOffer(\Cocorico\CoreBundle\Entity\DirectoryOffer $offer)
    {
        if (!$this->offers->contains($offer)) {
            $offer->setDirectory($this);
            $this->offers->add($offer);
        }

        return $this;
    }

    /**
     * Remove offer.
     *
     * @param \Cocorico\CoreBundle\Entity\DirectoryOffer $offer
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOffer(\Cocorico\CoreBundle\Entity\DirectoryOffer $offer)
    {
        if ($this->offers->contains($offer)) {
            $this->offers->removeElement($offer);
        }
        return $this;
    }

    /**
     * Get offers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * Set isCoContracting.
     *
     * @param bool|null $isCoContracting
     *
     * @return Directory
     */
    public function setIsCoContracting($isCoContracting = null)
    {
        $this->isCoContracting = $isCoContracting;

        return $this;
    }

    /**
     * Get isCoContracting.
     *
     * @return bool|null
     */
    public function getIsCoContracting()
    {
        return $this->isCoContracting;
    }

    /**
     * @param int  $minImages
     * @param bool $strict
     *
     * @return array
     */
    public function getCompletionInformations($strict = true)
    {
        return [
            "description" => $this->getDescription() ? 1 : 0,
            "website" => $this->getWebsite() ? 1 : 0,
            "prestaType" => $this->getPrestaType()->get() ? 1 : 0,
            "cocontracting" => $this->getIsCoContracting() ? 1 : 0,
            "range" => $this->getPolRange() || $this->getRange() ? 1 : 0,
            "logos" => count($this->getImages()) > 0,
            "clientImages" => count($this->getClientImages()) > 0,
            "labels" => count($this->getLabels()) > 0,
            "offers" => count($this->getOffers()) > 0,
            "sectors" => count($this->getDirectoryListingCategories()) > 0,

        ];
    }


}

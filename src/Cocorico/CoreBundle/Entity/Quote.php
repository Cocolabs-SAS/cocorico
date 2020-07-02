<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Cocorico\CoreBundle\Model\QuoteOptionInterface;

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Quote
 * 
 * @ORM\Entity(repositoryClass="Cocorico\CoreBundle\Repository\QuoteRepository")
 *
 * @ORM\Table(name="quote",indexes={
 *    @ORM\Index(name="status_idx", columns={"status"}),
 *    @ORM\Index(name="created_at_idx", columns={"createdAt"}),
 *    @ORM\Index(name="updated_at_idx", columns={"updatedAt"})
 *  })
 *
 */


class Quote {
    /* Status */
    const STATUS_DRAFT = 0;
    const STATUS_NEW = 1;
    const STATUS_OK = 2;

    public static $statusValues = array (
        self::STATUS_DRAFT => 'entity.quote.status.draft',
        self::STATUS_NEW => 'entity.quote.status.new',
        self::STATUS_OK => 'entity.quote.status.ok'
    );

    public static $visibleStatus = array (
        self::STATUS_DRAFT,
        self::STATUS_NEW,
        self::STATUS_OK
    );

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     * @var integer
     */
    private $id;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="quotes", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\CoreBundle\Entity\QuoteUserAddress", mappedBy="quote", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Listing", inversedBy="quotes")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $listing;

    /**
     * @ORM\Column(name="frequency_hours", type="integer", nullable=true)
     *
     * @var int|null
     */
    private $frequency_hours;

    /**
     * @ORM\Column(name="frequency_period", type="smallint", nullable=true)
     *
     * @var int|null
     */
    private $frequency_period;

    /**
     * @ORM\Column(name="surface_m2", type="integer", nullable=true)
     *
     * @var int|null
     */
    private $surface_m2;

    /**
     * @ORM\Column(name="surface_type", type="smallint", nullable=true)
     *
     * @var int|null
     */
    private $surface_type;
    /**
     * Initial quote communication
     *
     * @ORM\Column(name="communication", type="text", length=65535, nullable=false)
     *
     * @var string
     */
    private $communication;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\MessageBundle\Entity\Thread", mappedBy="quote", cascade={"remove"}, orphanRemoval=true)
     */
    private $thread;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Model\QuoteOptionInterface", mappedBy="quote", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $options;

    /**
     * @ORM\Column(name="status", type="smallint")
     *
     * @var integer
     */
    private $status;

    /**
     * @ORM\Column(name="validated", type="boolean")
     *
     * @var boolean
     */
    private $validated = false;

    /**
     * @ORM\Column(name="time_zone_asker", type="string", length=100,  nullable=false)
     *
     * @var string
     */
    private $timeZoneAsker = 'Europe/Paris';

    /**
     * @ORM\Column(name="time_zone_offerer", type="string", length=100,  nullable=false)
     *
     * @var string
     */
    protected $timeZoneOfferer = 'Europe/Paris';


     /**
     * @var datetime $createdAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var datetime $updatedAt
     * 
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->options = new ArrayCollection();
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
     * Set user
     *
     * @param User|null $user
     * @return Quote
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user address
     *
     * @param QuoteUserAddress $userAddress
     * @return Quote
     */
    public function setUserAddress($userAddress)
    {
        $userAddress->setQuote($this);
        $this->userAddress = $userAddress;

        return $this;
    }

    /**
     * Get quote user address
     *
     * @return QuoteUserAddress
     */
    public function getUserAddress()
    {
        return $this->userAddress;
    }

    /**
     * Set listing
     *
     * @param Listing $listing
     * @return Quote
     */
    public function setListing(Listing $listing)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * Get listing
     *
     * @return Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param Thread $thread
     */
    public function setThread($thread)
    {
        $thread->setQuote($this);
        $this->thread = $thread;
    }

    /**
     * Add QuoteOption
     *
     * @param  QuoteOptionInterface $option
     * @return Quote
     */
    public function addOption($option)
    {
        if (!$this->options->contains($option)) {
            $option->setQuote($this);
            $this->options->add($option);
        }

        return $this;
    }

    /**
     * Remove QuoteOption
     *
     * @param QuoteOptionInterface $option
     */
    public function removeOption($option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get QuoteOptions
     *
     * @return Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ArrayCollection $options
     * @return $this
     */
    public function setOptions(ArrayCollection $options)
    {
        foreach ($options as $option) {
            $option->setQuote($this);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId() . " (" . $this->getListing() . ":" . $this->getStart()->format('d-m-Y') . ")";
    }

    /**
     * @return string
     */
    public function getTimeZoneAsker()
    {
        return $this->timeZoneAsker;
    }

    /**
     * @param string $timeZoneAsker
     */
    public function setTimeZoneAsker($timeZoneAsker)
    {
        $this->timeZoneAsker = $timeZoneAsker;
    }

    /**
     * @return string
     */
    public function getTimeZoneOfferer()
    {
        return $this->timeZoneOfferer;
    }

    /**
     * @param string $timeZoneOfferer
     */
    public function setTimeZoneOfferer($timeZoneOfferer)
    {
        $this->timeZoneOfferer = $timeZoneOfferer;
    }

    /**
     * Return visible status values
     *
     * @return array
     */
    public static function getVisibleStatusValues()
    {
        $status = array_intersect_key(self::$statusValues, array_flip(self::$visibleStatus));

        return $status;
    }

    // Older setters and getters, see if they can be replaced with options
    public function getFrequencyHours()
    {
        return $this->frequency_hours;
    }
    public function setFrequencyHours($hours)
    {
        // ok
    }

    public function getFrequencyPeriod()
    {
        return $this->frequency_period;
    }
    public function setFrequencyPeriod($period)
    {
        // ok
    }

    public function getSurfaceM2()
    {
        return $this->surface_m2;
    }
    public function setSurfaceM2($surface)
    {
        // ok
    }

    public function getSurfaceType()
    {
        return $this->surface_type;
    }
    public function setSurfaceType($type)
    {
        // ok
    }

    public function getCommunication()
    {
        return $this->surface_type;
    }
    public function setCommunication($type)
    {
        // ok
    }
}

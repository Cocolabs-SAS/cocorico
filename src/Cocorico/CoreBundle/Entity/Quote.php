<?php

namespace Cocorico\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Cocorico\CoreBundle\Model\QuoteOptionInterface;

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
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

    use ORMBehaviors\Timestampable\Timestampable;

    /* Status */
    const STATUS_DRAFT = 0;
    const STATUS_NEW = 1;
    const STATUS_QUOTE = 3;
    const STATUS_ACCEPTED = 4;
    const STATUS_CANCELED = 5;
    const STATUS_REFUSED_ASKER = 6;
    const STATUS_REFUSED_OFFERER = 7;
    const STATUS_PREQUOTE = 8;

    public static $statusValues = array (
        self::STATUS_DRAFT => 'entity.quote.status.draft',
        self::STATUS_NEW => 'entity.quote.status.new',
        self::STATUS_PREQUOTE => 'entity.quote.status.prequote',
        self::STATUS_QUOTE => 'entity.quote.status.quote',
        self::STATUS_ACCEPTED => 'entity.quote.status.accepted',
        self::STATUS_CANCELED => 'entity.quote.status.canceled',
        self::STATUS_REFUSED_ASKER => 'entity.quote.status.refused_asker',
        self::STATUS_REFUSED_OFFERER => 'entity.quote.status.refused_offerer'
    );

    public static $visibleStatus = array (
        self::STATUS_DRAFT,
        self::STATUS_NEW,
        self::STATUS_PREQUOTE,
        self::STATUS_QUOTE,
        self::STATUS_ACCEPTED,
        self::STATUS_CANCELED,
        self::STATUS_REFUSED_ASKER,
        self::STATUS_REFUSED_OFFERER
    );

    //Status for which quote can be created
    public static $newableStatus = array (
        self::STATUS_DRAFT
    );

    //Status when contact info can be displayed
    public static $canShowContactInfo = array(
        self::STATUS_PREQUOTE,
        self::STATUS_QUOTE,
        self::STATUS_ACCEPTED,
    );

    //Status for which quote can be canceled by asker
    public static $cancelableStatus = array(
        self::STATUS_NEW,
    );

    //Status for which quote can be expired
    public static $expirableStatus = array(
        self::STATUS_DRAFT,
        self::STATUS_NEW,
    );

    //Status for which quote can be refused by asker
    public static $refusableAskerStatus = array(
        self::STATUS_PREQUOTE,
        self::STATUS_QUOTE,
    );

    //Status for which quote can be accepted by asker
    public static $acceptableStatus = array(
        self::STATUS_QUOTE
    );

    //Status for which quote can be refused by offerer
    public static $refusableOffererStatus = array(
        self::STATUS_NEW,
        self::STATUS_PREQUOTE,
        self::STATUS_QUOTE
    );

    //Status for which quote can be validated
    public static $validatableStatus = array(
        self::STATUS_NEW
    );

    //Status for which contact details are hidden
    public static $hideContactStatus = array(
        self::STATUS_DRAFT,
        self::STATUS_NEW,
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
     * @ORM\Column(name="prestaStartDate", type="datetime")
     *
     * @var DateTime
     */
    private $prestaStartDate;

    /**
     * @ORM\Column(name="budget", type="integer", nullable=true)
     *
     * @var int|null
     */
    private $budget;

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
     * @ORM\Column(name="accepted_quote_at", type="datetime", nullable=true)
     *
     * @var DateTime
     */
    protected $acceptedQuoteAt;

    /**
     * @ORM\Column(name="refused_quote_at", type="datetime", nullable=true)
     *
     * @var DateTime
     */
    protected $refusedQuoteAt;


    /**
     * @ORM\Column(name="canceled_quote_at", type="datetime", nullable=true)
     *
     * @var DateTime
     */
    protected $canceledQuoteAt;

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
     * Set status
     *
     * @param  integer $status
     * @return BaseBooking
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new InvalidArgumentException(
                sprintf('Invalid value for booking.status : %s.', $status)
            );
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusText()
    {
        return self::$statusValues[$this->getStatus()];
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
        return (string)$this->getId() . " (" . $this->getListing() . ":" . $this->getPrestaStartDate()->format('d-m-Y') . ")";
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
        return $this->frequency_hours = $hours;
    }

    public function getFrequencyPeriod()
    {
        return $this->frequency_period;
    }
    public function setFrequencyPeriod($period)
    {
        $this->frequency_period = $period;
    }

    public function getSurfaceM2()
    {
        return $this->surface_m2;
    }
    public function setSurfaceM2($surface)
    {
        $this->surface = $surface;
    }

    public function getSurfaceType()
    {
        return $this->surface_type;
    }
    public function setSurfaceType($type)
    {
        $this->surface_type = $type;
    }

    /**
     * @return string $communication
     */
    public function getCommunication()
    {
        return $this->communication;
    }
    /**
     * @param string $communication
     */
    public function setCommunication($communication)
    {
        $this->communication = $communication;
    }

    /**
     * @return integer $budget
     */
    public function getBudget()
    {
        return $this->budget;
    }
    /**
     * @param integer $budget
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;
    }

    /**
     * @return datetime $prestaStartDate
     */
    public function getPrestaStartDate()
    {
        return $this->prestaStartDate;
    }
    /**
     * @param datetime $prestaStartDate
     */
    public function setPrestaStartDate($prestaStartDate)
    {
        $this->prestaStartDate = $prestaStartDate;
    }

    /**
     * @return boolean
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @param boolean $validated
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
    }

    /**
     * @return DateTime
     */
    public function getAcceptedQuoteAt()
    {
        return $this->acceptedQuoteAt;
    }

    /**
     * @param DateTime $acceptedQuoteAt
     */
    public function setAcceptedQuoteAt($acceptedQuoteAt)
    {
        $this->acceptedQuoteAt = $acceptedQuoteAt;
    }

    /**
     * @return DateTime
     */
    public function getRefusedQuoteAt()
    {
        return $this->refusedQuoteAt;
    }

    /**
     * @param DateTime $refusedQuoteAt
     */
    public function setRefusedQuoteAt($refusedQuoteAt)
    {
        $this->refusedQuoteAt = $refusedQuoteAt;
    }


    /**
     * @return DateTime
     */
    public function getCanceledQuoteAt()
    {
        return $this->canceledQuoteAt;
    }

    /**
     * @param DateTime $canceledQuoteAt
     */
    public function setCanceledQuoteAt($canceledQuoteAt)
    {
        $this->canceledQuoteAt = $canceledQuoteAt;
    }


    public function log($prefix = '')
    {
        echo "<br>Booking";
        if ($prefix) {
            echo "<br>$prefix";
        }

        echo '<br>Date: ';
        if ($this->getStart() && $this->getEnd()) {
            echo $this->getStart()->format('Y-m-d H:i').' / '.$this->getEnd()->format('Y-m-d H:i').'<br>';
        }


        echo 'Time: ';
        if ($this->getStartTime() && $this->getEndTime()) {
            echo $this->getStartTime()->format('Y-m-d H:i').' / '.$this->getEndTime()->format('Y-m-d H:i').'<br>';
        }
    }
 
}

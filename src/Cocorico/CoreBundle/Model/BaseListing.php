<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Component\Validator\Constraints as Assert;
use BitMask\BitMask;
use BitMask\BitMaskInterface;


/**
 * Listing
 *
 * @CocoricoAssert\Listing()
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseListing
{

    /* Status */
    const STATUS_NEW = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_INVALIDATED = 3;
    const STATUS_SUSPENDED = 4;
    const STATUS_DELETED = 5;
    const STATUS_TO_VALIDATE = 6;

    public static $statusValues = array(
        self::STATUS_NEW => 'entity.listing.status.new',
        self::STATUS_PUBLISHED => 'entity.listing.status.published',
        self::STATUS_INVALIDATED => 'entity.listing.status.invalidated',
        self::STATUS_SUSPENDED => 'entity.listing.status.suspended',
        self::STATUS_DELETED => 'entity.listing.status.deleted',
        self::STATUS_TO_VALIDATE => 'entity.listing.status.to_validate'
    );

    public static $visibleStatus = array(
        self::STATUS_NEW,
        self::STATUS_PUBLISHED,
        self::STATUS_INVALIDATED,
        self::STATUS_SUSPENDED,
        self::STATUS_TO_VALIDATE
    );

    /* Frequency period */
    const FREQUENCY_PERIOD_WEEK = 1;
    const FREQUENCY_PERIOD_MONTH = 1;

    public static $frequencyPeriodValues = array (
        self::FREQUENCY_PERIOD_WEEK => 'entity.listing.frequency_period.week',
        self::FREQUENCY_PERIOD_MONTH => 'entity.listing.frequency_period.month'
    );

    public static $visibleFrequencyPeriod = array(
        self::FREQUENCY_PERIOD_WEEK,
        self::FREQUENCY_PERIOD_MONTH
    );


    /* Surface types */
    CONST SURFACE_TYPE_WOOD = 1;
    CONST SURFACE_TYPE_CONCRETE = 2;

    public static $surfaceTypeValues = array (
        self::SURFACE_TYPE_WOOD => 'entity.listing.surface_type.wood',
        self::SURFACE_TYPE_CONCRETE => 'entity.listing.surface_type.concrete'
    );

    public static $visibleSurfaceType = array(
        self::SURFACE_TYPE_WOOD,
        self::SURFACE_TYPE_CONCRETE
    );


    /* Type */
    const TYPE_ONE = 1;
    const TYPE_TWO = 2;
    const TYPE_THREE = 3;

    public static $typeValues = array(
        self::TYPE_ONE => 'entity.listing.type.one',
        self::TYPE_TWO => 'entity.listing.type.two',
        self::TYPE_THREE => 'entity.listing.type.three'
    );

    /* Schedule */
    const SCHEDULE_BUSINESS_HOURS = 1 << 0;
    const SCHEDULE_BEFORE_OPENING = 1 << 1;
    const SCHEDULE_AFTER_CLOSING = 1 << 2;

    /* Cancellation policy */
    const CANCELLATION_POLICY_FLEXIBLE = 1;
    const CANCELLATION_POLICY_STRICT = 2;

    public static $cancellationPolicyValues = array(
        self::CANCELLATION_POLICY_FLEXIBLE => 'entity.listing.cancellation_policy.flexible',
        self::CANCELLATION_POLICY_STRICT => 'entity.listing.cancellation_policy.strict',
    );

    public static $cancellationPolicyDescriptions = array(
        self::CANCELLATION_POLICY_FLEXIBLE => 'entity.listing.cancellation_policy_desc.flexible',
        self::CANCELLATION_POLICY_STRICT => 'entity.listing.cancellation_policy_desc.strict',
    );

    /**
    * @ORM\Column(name="schedules", type="bitmask", nullable=true)
    * @var \Doctrine\DBAL\Types\Type\bitmask
    */
    protected $schedules = BitMaskType::class;

    /**
     * @ORM\Column(name="status", type="smallint", nullable=false)
     *
     * @var integer
     */
    protected $status = self::STATUS_NEW;

    /**
     * @ORM\Column(name="type", type="smallint", nullable=true)
     *
     * @var integer
     */
    protected $type;

    /**
     * @ORM\Column(name="price", type="decimal", precision=8, scale=0, nullable=true)
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @var integer|null
     */
    protected $price;

    /**
     * @ORM\Column(name="`range`", type="integer", nullable=true)
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @var integer|null
     */
    protected $range;

    /**
     * @ORM\Column(name="`url`", type="string", nullable=true)
     *
     * @var string|null
     */
    protected $url;

    /**
     *
     * @ORM\Column(name="certified", type="boolean", nullable=true)
     *
     * @var boolean
     */
    protected $certified;

    /**
     *
     * @ORM\Column(name="min_duration", type="smallint", nullable=true)
     *
     * @var integer
     */
    protected $minDuration;

    /**
     *
     * @ORM\Column(name="max_duration", type="smallint", nullable=true)
     *
     * @var integer
     */
    protected $maxDuration;

    /**
     *
     * @ORM\Column(name="cancellation_policy", type="smallint", nullable=false)
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @var integer
     */
    protected $cancellationPolicy = self::CANCELLATION_POLICY_FLEXIBLE;


    /**
     * @ORM\Column(name="average_rating", type="smallint", nullable=true)
     *
     * @var integer
     */
    protected $averageRating;

    /**
     * @ORM\Column(name="comment_count", type="integer", nullable=true)
     *
     * @var integer
     */
    protected $commentCount = 0;

    /**
     * Admin notation
     *
     * @ORM\Column(name="admin_notation", type="decimal", precision=3, scale=1, nullable=true)
     *
     * @var float
     */
    protected $adminNotation;

    /**
     * @ORM\Column(name="availabilities_updated_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $availabilitiesUpdatedAt;

   /**
     * @ORM\Column(name="frequency_hours", type="integer", nullable=true)
     *
     * @var int|null
     */
    protected $frequencyHours;

    /**
     * @ORM\Column(name="frequency_period", type="smallint", nullable=true)
     *
     * @var int|null
     */
    protected $frequencyPeriod;

    /**
     * @ORM\Column(name="surface_m2", type="integer", nullable=true)
     *
     * @var int|null
     */
    protected $surfaceM2;

    /**
     * @ORM\Column(name="surface_type", type="smallint", nullable=true)
     *
     * @var int|null
     */
    protected $surfaceType;


    /**
     * Translation proxy
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Set status
     *
     * @param  integer $status
     * @return $this
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.status : %s.', $status)
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
     * Return available status for current status
     *
     * @param int $status
     *
     * @return array
     */
    public static function getAvailableStatusValues($status)
    {
        $availableStatus = array(self::STATUS_DELETED);

        if ($status == self::STATUS_NEW) {
            $availableStatus[] = self::STATUS_PUBLISHED;
        } elseif ($status == self::STATUS_PUBLISHED) {
            $availableStatus[] = self::STATUS_SUSPENDED;
        } elseif ($status == self::STATUS_INVALIDATED) {
            $availableStatus[] = self::STATUS_TO_VALIDATE;
        } elseif ($status == self::STATUS_SUSPENDED) {
            $availableStatus[] = self::STATUS_PUBLISHED;
        }

        //Prepend current status to visible status
        array_unshift($availableStatus, $status);

        //Construct associative array with keys equals to status values and values to label of status
        $status = array_intersect_key(
            self::$statusValues,
            array_flip($availableStatus)
        );

        return $status;
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
     * Set url
     *
     * @param  string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * Set price
     *
     * @param  integer $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPriceDecimal()
    {
        if (!$this->price) {
            return 0;
        }
        return $this->price / 100;
    }

    /**
     * Get offerer amount fees
     *
     * @param float $feeAsOfferer
     *
     * @return float
     */
    public function getAmountFeeAsOffererDecimal($feeAsOfferer)
    {
        return $this->getPriceDecimal() * $feeAsOfferer;
    }

    /**
     * Get amount to pay to offerer
     *
     * @param float $feeAsOfferer
     *
     * @return float
     */
    public function getAmountToPayToOffererDecimal($feeAsOfferer)
    {
        return $this->getPriceDecimal() - $this->getAmountFeeAsOffererDecimal($feeAsOfferer);
    }

    /**
     * Get amount to pay to offerer minus VAT when listing price is VAT excluded.
     *
     * Return the same result than getAmountToPayToOffererDecimal used with listing price VAT is included:
     * amountToPayVATIncluded = PriceVATIncluded - (PriceVATIncluded * feeAsOfferer)
     * amountToPayVATExcluded = amountToPayVATIncluded / (1 + vatRate)
     *
     * So :
     * amountToPayVATIncluded = ((price * (1 + vatRate)) - (price * (1 + vatRate) * feeAsOfferer))
     * amountToPayVATExcluded = amountToPayVATIncluded / (1 + vatRate)
     * amountToPayVATExcluded = price - price * feeAsOfferer
     * amountToPayVATExcluded = getAmountToPayToOffererDecimal
     *
     *
     * @param float $feeAsOfferer
     *
     * @return int
     */
    public function amountToPayToOffererForPriceExcludingVATDecimal($feeAsOfferer)
    {
        return $this->getAmountToPayToOffererDecimal($feeAsOfferer);
    }

    /**
     * Get offerer amount fees when listing price is VAT excluded.
     * Fees are computed on listing price VAT included
     *
     * @param float $feeAsOfferer
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountFeeAsOffererForPriceExcludingVATDecimal($feeAsOfferer, $vatRate)
    {
        return $this->getPriceDecimal() * (1 + $vatRate) * $feeAsOfferer;

    }

    /**
     * @return boolean
     */
    public function isCertified()
    {
        return $this->certified;
    }

    /**
     * @param boolean $certified
     */
    public function setCertified($certified)
    {
        $this->certified = $certified;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param  integer $type
     * @return $this
     */
    public function setType($type)
    {
        if (!in_array($type, array_keys(self::$typeValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.type : %s.', $type)
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get Type Text
     *
     * @return string
     */
    public function getTypeText()
    {
        return self::$typeValues[$this->getType()];
    }

    /**
     * Get certified
     *
     * @return boolean
     */
    public function getCertified()
    {
        return $this->certified;
    }

    /**
     * @return int
     */
    public function getMinDuration()
    {
        return $this->minDuration;
    }

    /**
     * @param int $minDuration
     */
    public function setMinDuration($minDuration)
    {
        $this->minDuration = $minDuration;
    }

    /**
     * @return int
     */
    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    /**
     * @param int $maxDuration
     */
    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;
    }

    /**
     * @return int
     */
    public function getCancellationPolicy()
    {
        return $this->cancellationPolicy;
    }

    /**
     * @param int $cancellationPolicy
     *
     * @return BaseListing
     */
    public function setCancellationPolicy($cancellationPolicy)
    {
        if (!in_array($cancellationPolicy, array_keys(self::$cancellationPolicyValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.status : %s.', $cancellationPolicy)
            );
            //$cancellationPolicy = self::CANCELLATION_POLICY_FLEXIBLE;
        }

        $this->cancellationPolicy = $cancellationPolicy;

        return $this;
    }

    /**
     * Get Cancellation Policy Text
     *
     * @return string
     */
    public function getCancellationPolicyText()
    {
        return self::$cancellationPolicyValues[$this->getCancellationPolicy()];
    }

    /**
     * Get Cancellation Policy Description
     *
     * @return string
     */
    public function getCancellationPolicyDescription()
    {
        return self::$cancellationPolicyDescriptions[$this->getCancellationPolicy()];
    }

    /**
     * Set averageRating
     *
     * @param  integer $averageRating
     * @return $this
     */
    public function setAverageRating($averageRating)
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    /**
     * Get averageRating
     *1
     *
     * @return integer
     */
    public function getAverageRating()
    {
        return $this->averageRating;
    }

    /**
     * Set commentCount
     *
     * @param  integer $commentCount
     * @return $this
     */
    public function setCommentCount($commentCount)
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    /**
     * Get commentCount
     *1
     *
     * @return integer
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    /**
     * @return float
     */
    public function getAdminNotation()
    {
        return $this->adminNotation;
    }

    /**
     * @param float $adminNotation
     */
    public function setAdminNotation($adminNotation)
    {
        $this->adminNotation = $adminNotation;
    }


    /**
     * @return \DateTime
     */
    public function getAvailabilitiesUpdatedAt()
    {
        return $this->availabilitiesUpdatedAt;
    }

    /**
     * @param \DateTime $availabilitiesUpdatedAt
     */
    public function setAvailabilitiesUpdatedAt($availabilitiesUpdatedAt)
    {
        $this->availabilitiesUpdatedAt = $availabilitiesUpdatedAt;
    }

    /**
     * Set frequencyHours.
     *
     * @param int|null $frequencyHours
     *
     * @return Test
     */
    public function setFrequencyHours($frequencyHours = null)
    {
        $this->frequencyHours = $frequencyHours;

        return $this;
    }

    /**
     * Get frequencyHours.
     *
     * @return int|null
     */
    public function getFrequencyHours()
    {
        return $this->frequencyHours;
    }

    /**
     * Set frequencyPeriod.
     *
     * @param int|null $frequencyPeriod
     *
     * @return $this
     */
    public function setFrequencyPeriod($frequencyPeriod = null)
    {
        if (!in_array($frequencyPeriod, array_keys(self::$frequencyPeriodValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.frequencyPeriod : %s.', $frequencyPeriod)
            );
        }

        $this->frequencyPeriod = $frequencyPeriod;

        return $this;
    }

    /**
     * Get frequencyPeriod.
     *
     * @return int|null
     */
    public function getFrequencyPeriod()
    {
        return $this->frequencyPeriod;
    }

    /**
     * Get Frequency Period Text
     *
     * @return string
     */
    public function getFrequencyPeriodText()
    {
        return self::$frequencyPeriodValues[$this->getFrequencyPeriod()];
    }

    /**
     * Return available frequency periods
     *
     * @param int $frequencyPeriod
     *
     * @return array
     */
    public static function getAvailableFrequencyPeriodValues($frequencyPeriod)
    {
        $availableFrequencyPeriod = array(self::FREQUENCY_PERIOD_WEEK, self::FREQUENCY_PERIOD_MONTH);


        array_unshift($availableFrequencyPeriod, $frequencyPeriod);
        $frequencyPeriod = array_intersect_key(
            self::$frequencyPeriodValues,
            array_flip($availableFrequencyPeriod)
        );

        return $frequencyPeriod;
    }


    /**
     * Set surfaceM2.
     *
     * @param int|null $surfaceM2
     *
     * @return $this
     */
    public function setSurfaceM2($surfaceM2)
    {
        $this->surfaceM2 = $surfaceM2;

        return $this;
    }

    /**
     * Get surfaceM2.
     *
     * @return int|null
     */
    public function getSurfaceM2()
    {
        return $this->surfaceM2;
    }

    /**
     * Set surfaceType.
     *
     * @param int|null $surfaceType
     *
     * @return $this
     */
    public function setSurfaceType($surfaceType = null)
    {
        if (!in_array($surfaceType, array_keys(self::$surfaceTypeValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.surfaceType : %s.', $surfaceType)
            );
        }

        $this->surfaceType = $surfaceType;

        return $this;
    }

    /**
     * Get surfaceType.
     *
     * @return int|null
     */
    public function getSurfaceType()
    {
        return $this->surfaceType;
    }


    /**
     * Get SurfaceType Text
     *
     * @return string
     */
    public function getSurfaceTypeText()
    {
        return self::$surfaceTypeValues[$this->getSurfaceType()];
    }

    /**
     * Get Schedules
     *
     */
    public function getSchedules()
    {
        if (is_string($this->schedules))
            {
            return new Bitmask();
            }

        return $this->schedules;
    }

    /**
     * Force convert schedule data type to int
     *
     */
    public function schedulesToInt() 
    {
        $this->schedules = $this->schedules->get();
    }

    /**
     * Set Schedules
     *
     * @return self
     */
    public function setSchedules(BitMaskInterface $schedules) : self
    {
        $this->schedules = $schedules;

        return $this;
    }

    /**
     * Enable Single Schedule
     *
     * @return self
     */
    public function enableSchedule($schedule) : self
    {

        $sc = $this->getSchedules();
        $sc->setBit($schedule);
        $this->schedules = $sc;
        return $this;
    }
    /**
     * Disable Single Schedule
     *
     * @return self
     */
    public function disableSchedule($schedule) : self
    {
        $sc = $this->getSchedules();
        $sc->unsetBit($schedule);
        $this->schedules = $sc;
        return $this;
    }
    public function hasSchedule() : bool
    {
        return $this->isScheduleBusinessHours()
            || $this->isScheduleBeforeOpening()
            || $this->isScheduleAfterClosing();
    }
    /**
     * Check schedule business hours
     *
     * @return bool
     */
    public function isScheduleBusinessHours() : bool
    {
        return $this->getSchedules()->isSetBit(static::SCHEDULE_BUSINESS_HOURS);
    }

    /*
     * Check schedule before opening
     *
     * @return bool
     */
    public function isScheduleBeforeOpening() : bool
    {
        return $this->getSchedules()->isSetBit(static::SCHEDULE_BEFORE_OPENING);
    }

    /**
     * Check schedule after closing
     *
     * @return bool
     */
    public function isScheduleAfterClosing() : bool
    {
        return $this->getSchedules()->isSetBit(static::SCHEDULE_AFTER_CLOSING);
    }

    /**
     * Check schedule business hours
     *
     * @param bool $set
     *
     * @return self
     */
    public function setScheduleBusinessHours($set) : self
    {
        return $set
            ? $this->enableSchedule(self::SCHEDULE_BUSINESS_HOURS)
            : $this->disableSchedule(self::SCHEDULE_BUSINESS_HOURS);
    }
    public function getScheduleBusinessHours() : bool {
        return $this->isScheduleBusinessHours();
    }

    /**
     * Check schedule Before Opening
     *
     * @param bool $set
     *
     * @return self
     */
    public function setScheduleBeforeOpening($set) : self
    {
        return $set
            ? $this->enableSchedule(self::SCHEDULE_BEFORE_OPENING)
            : $this->disableSchedule(self::SCHEDULE_BEFORE_OPENING);
    }
    public function getScheduleBeforeOpening() : bool {
        return $this->isScheduleBeforeOpening();
    }

    /**
     * Check schedule Before Opening
     *
     * @param bool $set
     *
     * @return self
     */
    public function setScheduleAfterClosing($set) : self
    {
        return $set
            ? $this->enableSchedule(self::SCHEDULE_AFTER_CLOSING)
            : $this->disableSchedule(self::SCHEDULE_AFTER_CLOSING);
    }
    public function getScheduleAfterClosing() : bool {
        return $this->isScheduleAfterClosing();
    }

    /**
     * Return available surfacetype for current surfacetype
     *
     * @param int $surfacetype
     *
     * @return array
     */
    public static function getAvailableSurfaceTypeValues($surfaceType)
    {
        $availableSurfaceType = array(self::SURFACE_TYPE_WOOD, self::SURFACE_TYPE_CONCRETE);

        //Prepend current surfaceType to visible surfaceType
        array_unshift($availableSurfaceType, $surfaceType);

        //Construct associative array with keys equals to surfaceType values and values to label of surfaceType
        $surfaceType = array_intersect_key(
            self::$surfaceTypeValues,
            array_flip($availableSurfaceType)
        );

        return $surfaceType;
    }

}

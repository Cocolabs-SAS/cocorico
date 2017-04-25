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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Validator\Constraints as CocoricoAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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


    /* Type */
    const TYPE_ONE = 1;
    const TYPE_TWO = 2;
    const TYPE_THREE = 3;

    public static $typeValues = array(
        self::TYPE_ONE => 'entity.listing.type.one',
        self::TYPE_TWO => 'entity.listing.type.two',
        self::TYPE_THREE => 'entity.listing.type.three'
    );

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
     * @ORM\Column(name="price", type="decimal", precision=8, scale=0, nullable=false)
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @var integer
     */
    protected $price;

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
     * @return Listing
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
     * Set price
     *
     * @param  integer $price
     * @return Listing
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
     * @return Listing
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
     * @return Listing
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
     * @return Listing
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


}

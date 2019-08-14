<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Entity;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\BookingBankWire;
use Cocorico\CoreBundle\Entity\BookingPayinRefund;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\ReviewBundle\Entity\Review;
use Cocorico\UserBundle\Model\BookingDepositRefundAsAskerInterface;
use Cocorico\UserBundle\Model\BookingDepositRefundAsOffererInterface;
use Cocorico\UserBundle\Model\ListingAlertInterface;
use Cocorico\UserBundle\Model\UserCardInterface;
use Cocorico\UserBundle\Validator\Constraints as CocoricoUserAssert;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\UserBundle\Model\User as BaseUser;
use InvalidArgumentException;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

//use Sonata\UserBundle\Entity\BaseUser;

/**
 * User.
 *
 * @CocoricoUserAssert\User()
 *
 * @ORM\Entity(repositoryClass="Cocorico\UserBundle\Repository\UserRepository")
 *
 * @UniqueEntity(
 *      fields={"email"},
 *      groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact", "default"},
 *      message="cocorico_user.email.already_used"
 * )
 *
 * @UniqueEntity(
 *      fields={"username"},
 *      groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact", "default"},
 *      message="cocorico_user.email.already_used"
 * )
 *
 * @ORM\Table(name="`user`",indexes={
 *    @ORM\Index(name="created_at_u_idx", columns={"createdAt"}),
 *    @ORM\Index(name="slug_u_idx", columns={"slug"}),
 *    @ORM\Index(name="enabled_idx", columns={"enabled"}),
 *    @ORM\Index(name="email_idx", columns={"email"})
 *  })
 */
class User extends BaseUser implements ParticipantInterface
{
    use ORMBehaviors\Timestampable\Timestampable;
    use ORMBehaviors\Translatable\Translatable;
    use ORMBehaviors\Sluggable\Sluggable;

    /**
     * Fix missing validation on translations fields
     * @Assert\Valid
     */
    protected $translations;


    const PERSON_TYPE_NATURAL = 1;
    const PERSON_TYPE_LEGAL = 2;

    public static $personTypeValues = array(
        self::PERSON_TYPE_NATURAL => 'entity.user.person_type.natural',
        self::PERSON_TYPE_LEGAL => 'entity.user.person_type.legal',
    );

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     *
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\Email(message="cocorico_user.email.invalid", strict=true, groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"})
     *
     * @Assert\NotBlank(message="cocorico_user.email.blank", groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="cocorico_user.username.short",
     *     maxMessage="cocorico_user.username.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"}
     * )
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="person_type", type="smallint", nullable=false)
     *
     * @Assert\NotNull
     */
    protected $personType = self::PERSON_TYPE_NATURAL;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @ORM\Column(name="last_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.last_name.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"
     * })
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.last_name.short",
     *     maxMessage="cocorico_user.last_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"}
     * )
     */
    protected $lastName;

    /**
     * @ORM\Column(name="first_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.first_name.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"
     * })
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.first_name.short",
     *     maxMessage="cocorico_user.first_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileBankAccount"}
     * )
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_prefix", type="string", length=6, nullable=true)
     */
    protected $phonePrefix = '+33';

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=16, nullable=true)
     */
    protected $phone;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="birthday", type="date")
     *
     * @Assert\NotBlank(message="cocorico_user.birthday.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfileBankAccount"
     * })
     */
    protected $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=3, nullable=true)
     */
    protected $nationality = 'FR';

    /**
     * @var string
     *
     * @ORM\Column(name="country_of_residence", type="string", length=3, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.country_of_residence.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfileBankAccount"
     * })
     */
    protected $countryOfResidence = 'FR';

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=50, nullable=true)
     */
    protected $profession;

    /**
     * @var string
     *
     * @ORM\Column(name="iban", type="string", length=45, nullable=true)
     *
     * @Assert\Iban(message = "cocorico_user.iban.invalid", groups={
     *  "CocoricoProfileBankAccount"
     * }))
     *
     * @Assert\NotBlank(message="cocorico_user.iban.blank", groups={
     *  "CocoricoProfileBankAccount"
     * })
     */
    protected $iban;

    /**
     * @var string
     *
     * @ORM\Column(name="bic", type="string", length=25, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.bic.blank", groups={
     *  "CocoricoProfileBankAccount"
     * })
     */
    protected $bic;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_owner_name", type="string", length=100, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.bank_owner_name.blank", groups={
     *  "CocoricoProfileBankAccount"
     * })
     */
    protected $bankOwnerName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_owner_address", type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.bank_owner_address.blank", groups={
     *  "CocoricoProfileBankAccount"
     * })
     */
    protected $bankOwnerAddress;

    /**
     * @ORM\Column(name="annual_income", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @var int
     */
    protected $annualIncome;

    /**
     * @Assert\Length(
     *      min = 6,
     *      minMessage = "{{ limit }}cocorico_user.password.short",
     * )
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @ORM\Column(name="phone_verified", type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $phoneVerified = false;

    /**
     * @ORM\Column(name="email_verified", type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $emailVerified = false;

    /**
     * @ORM\Column(name="id_card_verified", type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $idCardVerified = false;

    /**
     * @ORM\Column(name="nb_bookings_offerer", type="smallint", nullable=true)
     *
     * @var int
     */
    protected $nbBookingsOfferer;

    /**
     * @ORM\Column(name="nb_bookings_asker", type="smallint", nullable=true)
     *
     * @var int
     */
    protected $nbBookingsAsker;

    /**
     * @ORM\Column(name="fee_as_asker", type="smallint", nullable=true)
     *
     * @var int Percent
     */
    protected $feeAsAsker;

    /**
     * @ORM\Column(name="fee_as_offerer", type="smallint", nullable=true)
     *
     * @var int Percent
     */
    protected $feeAsOfferer;

    /**
     * @ORM\Column(name="average_rating_as_asker", type="smallint", nullable=true)
     *
     * @var int
     */
    protected $averageAskerRating;

    /**
     * @ORM\Column(name="average_rating_as_offerer", type="smallint", nullable=true)
     *
     * @var int
     */
    protected $averageOffererRating;

    /**
     * @ORM\Column(name="mother_tongue", type="string", length=5, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.motherTongue.blank", groups={"CocoricoProfile"})
     *
     * @var string
     */
    protected $motherTongue;

    /**
     * @ORM\Column(name="answer_delay", type="integer", nullable=true)
     *
     * @var int
     */
    protected $answerDelay;

    /**
     * @ORM\Column(name="time_zone", type="string", length=100,  nullable=false)
     *
     * @var string
     */
    protected $timeZone = 'UTC';

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\MessageBundle\Entity\Message", mappedBy="sender", cascade={"remove"}, orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ReviewBundle\Entity\Review", mappedBy="reviewBy", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "desc"})
     */
    private $reviewsBy;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ReviewBundle\Entity\Review", mappedBy="reviewTo", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "desc"})
     */
    private $reviewsTo;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\UserBundle\Entity\UserFacebook", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $userFacebook;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\Listing", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var Listing[]
     */
    private $listings;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\UserBundle\Entity\UserAddress", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"type" = "asc"})
     *
     * @var UserAddress[]
     */
    private $addresses;

    /**
     * For Asserts : @see \Cocorico\UserBundle\Validator\Constraints\UserValidator.
     *
     * @ORM\OneToMany(targetEntity="UserImage", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "asc"})
     *
     * @var UserImage[]
     */
    protected $images;

    /**
     * @ORM\OneToMany(targetEntity="UserLanguage", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var UserLanguage[]
     */
    protected $languages;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\Booking", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var Booking[]
     */
    protected $bookings;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\BookingBankWire", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var BookingBankWire[]
     */
    private $bookingBankWires;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\BookingPayinRefund", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var BookingPayinRefund[]
     */
    private $bookingPayinRefunds;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\UserBundle\Model\ListingAlertInterface", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $listingAlerts;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\UserBundle\Model\BookingDepositRefundAsAskerInterface", mappedBy="asker", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $bookingDepositRefundsAsAsker;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\UserBundle\Model\BookingDepositRefundAsOffererInterface", mappedBy="offerer", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $bookingDepositRefundsAsOfferer;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\UserBundle\Model\UserCardInterface", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"expirationDate" = "asc"})
     *
     * @var UserCardInterface[]
     */
    private $cards;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->listings = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->reviewsBy = new ArrayCollection();
        $this->reviewsTo = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->bookingBankWires = new ArrayCollection();
        $this->bookingPayinRefunds = new ArrayCollection();
        $this->listingAlerts = new ArrayCollection();
        $this->bookingDepositRefundsAsAsker = new ArrayCollection();
        $this->bookingDepositRefundsAsOfferer = new ArrayCollection();
        $this->cards = new ArrayCollection();
        parent::__construct();
    }

    public function getSluggableFields()
    {
        return ['firstName', 'id'];
    }


    /**
     * Translation proxy.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
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
     * Set PersonType.
     *
     * @param int $personType
     *
     * @return User
     */
    public function setPersonType($personType)
    {
        if (!in_array($personType, array_keys(self::$personTypeValues))) {
            throw new InvalidArgumentException(
                sprintf('Invalid value for user.person_type : %s.', $personType)
            );
        }

        $this->personType = $personType;

        return $this;
    }

    /**
     * Get personType.
     *
     * @return int
     */
    public function getPersonType()
    {
        if (!$this->personType) {
            $this->personType = self::PERSON_TYPE_NATURAL;
        }

        return $this->personType;
    }

    /**
     * Get personType Text.
     *
     * @return string
     */
    public function getPersonTypeText()
    {
        return self::$personTypeValues[$this->getPersonType()];
    }

    /**
     * Get companyName.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set companyName.
     *
     * @param string $companyName
     *
     * @return User
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->firstName . ' ' . ucfirst(substr($this->lastName, 0, 1) . '.');
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
    }

    /**
     * Set phoneVerified.
     *
     * @param bool $phoneVerified
     *
     * @return User
     */
    public function setPhoneVerified($phoneVerified)
    {
        $this->phoneVerified = $phoneVerified;

        return $this;
    }

    /**
     * Get phoneVerified.
     *
     * @return bool
     */
    public function getPhoneVerified()
    {
        return $this->phoneVerified;
    }

    /**
     * Set emailVerified.
     *
     * @param bool $emailVerified
     *
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * Get emailVerified.
     *
     * @return bool
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * Set idCardVerified.
     *
     * @param bool $idCardVerified
     *
     * @return User
     */
    public function setIdCardVerified($idCardVerified)
    {
        $this->idCardVerified = $idCardVerified;

        return $this;
    }

    /**
     * Get idCardVerified.
     *
     * @return bool
     */
    public function getIdCardVerified()
    {
        return $this->idCardVerified;
    }

    /**
     * Set nbBookingsOfferer.
     *
     * @param int $nbBookingsOfferer
     *
     * @return User
     */
    public function setNbBookingsOfferer($nbBookingsOfferer)
    {
        $this->nbBookingsOfferer = $nbBookingsOfferer;

        return $this;
    }

    /**
     * Get nbBookingsOfferer.
     *
     * @return int
     */
    public function getNbBookingsOfferer()
    {
        return $this->nbBookingsOfferer;
    }

    /**
     * @return int
     */
    public function getNbBookingsAsker()
    {
        return $this->nbBookingsAsker;
    }

    /**
     * @param int $nbBookingsAsker
     */
    public function setNbBookingsAsker($nbBookingsAsker)
    {
        $this->nbBookingsAsker = $nbBookingsAsker;
    }

    /**
     * @return int
     */
    public function getFeeAsAsker()
    {
        return $this->feeAsAsker;
    }

    /**
     * @param int $feeAsAsker
     */
    public function setFeeAsAsker($feeAsAsker)
    {
        $this->feeAsAsker = $feeAsAsker;
    }

    /**
     * @return int
     */
    public function getFeeAsOfferer()
    {
        return $this->feeAsOfferer;
    }

    /**
     * @param int $feeAsOfferer
     */
    public function setFeeAsOfferer($feeAsOfferer)
    {
        $this->feeAsOfferer = $feeAsOfferer;
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param DateTime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param string $nationality
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    /**
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * @param string $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getBankOwnerName()
    {
        return $this->bankOwnerName;
    }

    /**
     * @param string $bankOwnerName
     */
    public function setBankOwnerName($bankOwnerName)
    {
        $this->bankOwnerName = $bankOwnerName;
    }

    /**
     * @return string
     */
    public function getBankOwnerAddress()
    {
        return $this->bankOwnerAddress;
    }

    /**
     * @param string $bankOwnerAddress
     */
    public function setBankOwnerAddress($bankOwnerAddress)
    {
        $this->bankOwnerAddress = $bankOwnerAddress;
    }

    /**
     * @return int
     */
    public function getAnnualIncome()
    {
        return $this->annualIncome;
    }

    /**
     * @param int $annualIncome
     */
    public function setAnnualIncome($annualIncome)
    {
        $this->annualIncome = $annualIncome;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhonePrefix()
    {
        return $this->phonePrefix;
    }

    /**
     * @param string $phonePrefix
     */
    public function setPhonePrefix($phonePrefix)
    {
        $this->phonePrefix = $phonePrefix;
    }

    /**
     * @return string
     */
    public function getCountryOfResidence()
    {
        return $this->countryOfResidence;
    }

    /**
     * @param string $countryOfResidence
     */
    public function setCountryOfResidence($countryOfResidence)
    {
        $this->countryOfResidence = $countryOfResidence;
    }

    /**
     * Set averageAskerRating.
     *
     * @param int $averageAskerRating
     *
     * @return $this
     */
    public function setAverageAskerRating($averageAskerRating)
    {
        $this->averageAskerRating = $averageAskerRating;

        return $this;
    }

    /**
     * Get averageAskerRating.
     *
     * @return int
     */
    public function getAverageAskerRating()
    {
        return $this->averageAskerRating;
    }

    /**
     * Set averageOffererRating.
     *
     * @param int $averageOffererRating
     *
     * @return $this
     */
    public function setAverageOffererRating($averageOffererRating)
    {
        $this->averageOffererRating = $averageOffererRating;

        return $this;
    }

    /**
     * Get averageOffererRating.
     *
     * @return int
     */
    public function getAverageOffererRating()
    {
        return $this->averageOffererRating;
    }

    /**
     * Set answerDelay.
     *
     * @param int $answerDelay
     *
     * @return $this
     */
    public function setAnswerDelay($answerDelay)
    {
        $this->answerDelay = $answerDelay;

        return $this;
    }

    /**
     * Get answerDelay.
     *
     * @return int
     */
    public function getAnswerDelay()
    {
        return $this->answerDelay;
    }

    /**
     * @return string
     */
    public function getMotherTongue()
    {
        return $this->motherTongue;
    }

    /**
     * @param string $motherTongue
     */
    public function setMotherTongue($motherTongue)
    {
        $this->motherTongue = $motherTongue;
    }

    /**
     * @return mixed
     */
    public function getUserFacebook()
    {
        return $this->userFacebook;
    }

    /**
     * @param userFacebook $userFacebook
     */
    public function setUserFacebook($userFacebook)
    {
        $userFacebook->setUser($this);
        $this->userFacebook = $userFacebook;
    }

    public function getFullName()
    {
        return implode(' ', array_filter(array($this->getFirstName(), $this->getLastName())));
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * Add listings.
     *
     * @param Listing $listing
     *
     * @return User
     */
    public function addListing(Listing $listing)
    {
        $this->listings[] = $listing;

        return $this;
    }

    /**
     * Remove listings.
     *
     * @param Listing $listing
     */
    public function removeListing(Listing $listing)
    {
        $this->listings->removeElement($listing);
    }

    /**
     * Get listings.
     *
     * @return Listing[]|ArrayCollection
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * Add images.
     *
     * @param UserImage $image
     *
     * @return $this
     */
    public function addImage(UserImage $image)
    {
        $image->setUser($this); //Because the owning side of this relation is user image
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove images.
     *
     * @param UserImage $image
     */
    public function removeImage(UserImage $image)
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
     * Add language.
     *
     * @param UserLanguage $language
     *
     * @return $this
     */
    public function addLanguage(UserLanguage $language)
    {
        $language->setUser($this);
        $this->languages[] = $language;

        return $this;
    }

    /**
     * Remove language.
     *
     * @param UserLanguage $language
     */
    public function removeLanguage(UserLanguage $language)
    {
        $this->languages->removeElement($language);
    }

    /**
     * Get languages.
     *
     * @return ArrayCollection|UserLanguage[]
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return ArrayCollection|Booking[]
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * @param ArrayCollection|Booking[] $bookings
     */
    public function setBookings(ArrayCollection $bookings)
    {
        foreach ($bookings as $booking) {
            $booking->setUser($this);
        }

        $this->bookings = $bookings;
    }

    /**
     * Does the user has booking as asker
     *
     * @return ArrayCollection|Booking[]
     */
    public function getBookingAsAsker()
    {
        return $this->getBookings();
    }

    /**
     * Does the user has booking as offerer
     *
     * @return ArrayCollection|Booking[]
     */
    public function getBookingAsOfferer()
    {
        $bookings = new ArrayCollection();
        $listings = $this->getListings();
        if ($listings->count()) {
            foreach ($listings as $listing) {
                foreach ($listing->getBookings() as $booking) {
                    $bookings->add($booking);
                }
            }
        }

        return $bookings;
    }

    /**
     * Does the user has booking in progress (some money operations still to be made (withdrawals, refund, ...))
     *
     * @return bool
     */
    public function hasBookingsInProgress()
    {
        $bookingsAsAsker = $this->getBookingAsAsker();
        $bookingsAsOfferer = $this->getBookingAsOfferer();

        /** @var Booking[] $bookings */
        $bookings = new ArrayCollection(array_merge($bookingsAsAsker->toArray(), $bookingsAsOfferer->toArray()));

        foreach ($bookings as $index => $booking) {
            if ($booking->getStatus() == Booking::STATUS_NEW) {
                return true;
            } elseif ($booking->getStatus() == Booking::STATUS_PAYMENT_REFUSED) {
                return true;
            } elseif ($booking->getStatus() == Booking::STATUS_PAYED) {
                //If there is no bank wire or there is a bank wire not payed
                $bankWire = $booking->getBankWire();
                if (!$bankWire || ($bankWire &&
                        ($bankWire->getStatus() != BookingBankWire::STATUS_PAYED || !$bankWire->getMangopayPayoutId())
                    )
                ) {
                    return true;
                }
            } elseif ($booking->getStatus() == Booking::STATUS_CANCELED_ASKER) {
                //If there is a bank wire not payed
                $bankWire = $booking->getBankWire();
                if (($bankWire &&
                    ($bankWire->getStatus() != BookingBankWire::STATUS_PAYED || !$bankWire->getMangopayPayoutId())
                )
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return ArrayCollection|Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ArrayCollection|Message[] $messages
     */
    public function setMessages(ArrayCollection $messages)
    {
        foreach ($messages as $message) {
            $message->setSender($this);
        }

        $this->messages = $messages;
    }

    /**
     * @return mixed
     */
    public function getReviewsBy()
    {
        return $this->reviewsBy;
    }

    /**
     * @param ArrayCollection|Message[] $reviewsBy
     */
    public function setReviewsBy(ArrayCollection $reviewsBy)
    {
        foreach ($reviewsBy as $review) {
            $review->setReviewBy($this);
        }

        $this->reviewsBy = $reviewsBy;
    }

    /**
     * @return mixed
     */
    public function getReviewsTo()
    {
        return $this->reviewsTo;
    }

    /**
     * @param ArrayCollection|Review[] $reviewsTo
     */
    public function setReviewsTo(ArrayCollection $reviewsTo)
    {
        foreach ($reviewsTo as $review) {
            $review->setReviewTo($this);
        }

        $this->reviewsTo = $reviewsTo;
    }

    /**
     * Add Address.
     *
     * @param UserAddress $address
     *
     * @return User
     */
    public function addAddress(UserAddress $address)
    {
        if (!$this->addresses->contains($address)) {
            $address->setUser($this);
            $this->addresses->add($address);
        }

        return $this;
    }

    /**
     * Remove Address.
     *
     * @param UserAddress $address
     */
    public function removeAddress(UserAddress $address)
    {
        $this->addresses->removeElement($address);
        $address->setUser(null);
    }

    /**
     * Get addresses ordered by type.
     *
     * @return Collection|UserAddress[]
     */
    public function getAddresses()
    {
        $addresses = $this->addresses->toArray();
        uasort(
            $addresses,
            function (UserAddress $a, UserAddress $b) {
                if ($a->getType() == $b->getType()) {
                    return 0;
                }

                return ($a->getType() < $b->getType()) ? -1 : 1;
            }
        );

        return new ArrayCollection($addresses);
    }

    /**
     * Get addresses of type.
     *
     * @param int $type
     *
     * @return UserAddress[]|Collection
     */
    public function getAddressesOfType($type)
    {
        return $this->addresses->filter(
            function (UserAddress $address) use ($type) {
                return $address->getType() == $type;
            }
        );
    }

    /**
     * @return BookingBankWire[]
     */
    public function getBookingBankWires()
    {
        return $this->bookingBankWires;
    }

    /**
     * @param ArrayCollection|BookingBankWire[] $bookingBankWires
     */
    public function setBookingBankWires(ArrayCollection $bookingBankWires)
    {
        foreach ($bookingBankWires as $bookingBankWire) {
            $bookingBankWire->setUser($this);
        }

        $this->bookingBankWires = $bookingBankWires;
    }

    /**
     * @return BookingPayinRefund[]
     */
    public function getBookingPayinRefunds()
    {
        return $this->bookingPayinRefunds;
    }

    /**
     * @param ArrayCollection|BookingPayinRefund[] $bookingPayinRefunds
     */
    public function setBookingPayinRefunds(ArrayCollection $bookingPayinRefunds)
    {
        foreach ($bookingPayinRefunds as $bookingPayinRefund) {
            $bookingPayinRefund->setUser($this);
        }

        $this->bookingPayinRefunds = $bookingPayinRefunds;
    }

    /**
     * Add ListingAlert.
     *
     * @param ListingAlertInterface $listingAlert
     *
     * @return User
     */
    public function addListingAlert($listingAlert)
    {
        $listingAlert->setUser($this);
        $this->listingAlerts[] = $listingAlert;

        return $this;
    }

    /**
     * Remove ListingAlert.
     *
     * @param ListingAlertInterface $listingAlert
     */
    public function removeListingAlert($listingAlert)
    {
        $this->listingAlerts->removeElement($listingAlert);
    }

    /**
     * Get ListingAlerts.
     *
     * @return ArrayCollection
     */
    public function getListingAlerts()
    {
        return $this->listingAlerts;
    }

    /**
     * @param ArrayCollection $listingAlerts
     *
     * @return $this
     */
    public function setListingAlerts(ArrayCollection $listingAlerts)
    {
        foreach ($listingAlerts as $listingAlert) {
            $listingAlert->setUser($this);
        }

        $this->listingAlerts = $listingAlerts;

        return $this;
    }


    /**
     * Add BookingDepositRefundAsAsker.
     *
     * @param BookingDepositRefundAsAskerInterface $bookingDepositRefundAsAsker
     *
     * @return User
     */
    public function addBookingDepositRefundAsAsker($bookingDepositRefundAsAsker)
    {
        $bookingDepositRefundAsAsker->setAsker($this);
        $this->bookingDepositRefundsAsAsker[] = $bookingDepositRefundAsAsker;

        return $this;
    }

    /**
     * Remove BookingDepositRefundAsAsker.
     *
     * @param BookingDepositRefundAsAskerInterface $bookingDepositRefundAsAsker
     */
    public function removeBookingDepositRefundAsAsker($bookingDepositRefundAsAsker)
    {
        $this->bookingDepositRefundsAsAsker->removeElement($bookingDepositRefundAsAsker);
    }

    /**
     * Get BookingDepositRefundsAsAsker.
     *
     * @return ArrayCollection
     */
    public function getBookingDepositRefundsAsAsker()
    {
        return $this->bookingDepositRefundsAsAsker;
    }

    /**
     * @param ArrayCollection $bookingDepositRefundsAsAsker
     *
     * @return $this
     */
    public function setBookingDepositRefundsAsAsker(ArrayCollection $bookingDepositRefundsAsAsker)
    {
        foreach ($bookingDepositRefundsAsAsker as $bookingDepositRefundAsAsker) {
            $bookingDepositRefundAsAsker->setAsker($this);
        }

        $this->bookingDepositRefundsAsAsker = $bookingDepositRefundsAsAsker;

        return $this;
    }


    /**
     * Add BookingDepositRefundAsOfferer.
     *
     * @param BookingDepositRefundAsOffererInterface $bookingDepositRefundAsOfferer
     *
     * @return User
     */
    public function addBookingDepositRefundAsOfferer($bookingDepositRefundAsOfferer)
    {
        $bookingDepositRefundAsOfferer->setOfferer($this);
        $this->bookingDepositRefundsAsOfferer[] = $bookingDepositRefundAsOfferer;

        return $this;
    }

    /**
     * Remove BookingDepositRefundAsOfferer.
     *
     * @param BookingDepositRefundAsOffererInterface $bookingDepositRefundAsOfferer
     */
    public function removeBookingDepositRefundAsOfferer($bookingDepositRefundAsOfferer)
    {
        $this->bookingDepositRefundsAsOfferer->removeElement($bookingDepositRefundAsOfferer);
    }

    /**
     * Get BookingDepositRefundsAsOfferer.
     *
     * @return ArrayCollection
     */
    public function getBookingDepositRefundsAsOfferer()
    {
        return $this->bookingDepositRefundsAsOfferer;
    }

    /**
     * @param ArrayCollection $bookingDepositRefundsAsOfferer
     *
     * @return $this
     */
    public function setBookingDepositRefundsAsOfferer(ArrayCollection $bookingDepositRefundsAsOfferer)
    {
        foreach ($bookingDepositRefundsAsOfferer as $bookingDepositRefundAsOfferer) {
            $bookingDepositRefundAsOfferer->setAsker($this);
        }

        $this->bookingDepositRefundsAsOfferer = $bookingDepositRefundsAsOfferer;

        return $this;
    }


    /**
     * Add UserCard.
     *
     * @param UserCardInterface $card
     *
     * @return User
     */
    public function addCard($card)
    {
        $card->setUser($this);
        $this->cards[] = $card;

        return $this;
    }

    /**
     * Remove UserCard.
     *
     * @param UserCardInterface $card
     */
    public function removeCard($card)
    {
        $this->cards->removeElement($card);
    }

    /**
     * Get UserCards.
     *
     * @return ArrayCollection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @param UserCardInterface[]|ArrayCollection $cards
     *
     * @return $this
     */
    public function setCards($cards)
    {
        foreach ($cards as $card) {
            $card->setUser($this);
        }

        $this->cards = $cards;

        return $this;
    }


    /**
     * @return UserCardInterface[]|Collection
     */
    public function getCardsActive()
    {
        return $this->cards->filter(
            function (UserCardInterface $element) {
                return $element->isActive();// && $element->getValidity() == UserCardInterface::VALIDITY_VALID
            }
        );
    }


    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * @param int  $minImages
     * @param bool $strict
     *
     * @return array
     */
    public function getCompletionInformations($minImages, $strict = true)
    {
        return array(
            'description' => (
                ($strict && $this->getDescription()) ||
                (!$strict && strlen($this->getDescription()) > 250)
            ) ? 1 : 0,
            'image' => (
                ($strict && count($this->getImages()) >= $minImages) ||
                (!$strict && count($this->getImages()) > $minImages)
            ) ? 1 : 0,
        );
    }

    /**
     * Guess preferred site language from motherTongue, and spoken languages and  sites locales enabled.
     *
     * todo: Add "preferred language" field to user entity and set it by default to mother tongue while registration, add it to editable fields and add it to the checked fields of this method.
     *
     * @param array  $siteLocales
     * @param string $defaultLocale
     *
     * @return string
     */
    public function guessPreferredLanguage($siteLocales, $defaultLocale)
    {
        if ($this->getMotherTongue() && in_array($this->getMotherTongue(), $siteLocales)) {
            return $this->getMotherTongue();
        } elseif ($this->getLanguages()->count()) {
            foreach ($this->getLanguages() as $language) {
                if (in_array($language->getCode(), $siteLocales)) {
                    return $language->getCode();
                }
            }
        }

        return $defaultLocale;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback(groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact", "default"},)
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->personType == self::PERSON_TYPE_LEGAL && empty($this->companyName)) {
            $context->buildViolation('cocorico_user.company_name.blank')
                ->atPath('companyName')
                ->setTranslationDomain('validators')
                ->addViolation();
        }
    }

    /**
     * To add impersonating link into admin :
     *
     * @return User
     */
    public function getImpersonating()
    {
        return $this;
    }
}

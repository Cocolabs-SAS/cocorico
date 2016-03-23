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

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserFacebook
 *
 * @ORM\Entity(repositoryClass="Cocorico\UserBundle\Repository\UserFacebookRepository")
 *
 * @UniqueEntity(
 *      fields={"email"},
 *      message="cocorico_user.email.already_used"
 * )
 *
 * @ORM\Table(name="`user_facebook`", indexes={
 *    @ORM\Index(name="facebook_id_idx", columns={"facebook_id"})
 *  })
 *
 */
class UserFacebook
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="userFacebook")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", length=100, nullable=false)
     */
    protected $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=false)
     */
    protected $link;

    /**
     * @var string
     *
     * @Assert\Email(message="cocorico_user.email.invalid", groups={"CocoricoRegistration", "CocoricoProfile"})
     *
     * @Assert\NotBlank(message="cocorico_user.email.blank", groups={"CocoricoRegistration", "CocoricoProfile"})
     *
     */
    protected $email;

    /**
     * @ORM\Column(name="last_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.last_name.blank", groups={"CocoricoRegistration", "CocoricoProfile"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.last_name.short",
     *     maxMessage="cocorico_user.last_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile"}
     * )
     */
    protected $lastName;

    /**
     * @ORM\Column(name="first_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.first_name.blank", groups={"CocoricoRegistration", "CocoricoProfile"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.first_name.short",
     *     maxMessage="cocorico_user.first_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile"}
     * )
     */
    protected $firstName;

    /**
     * @var \DateTime $birthday
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    protected $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(name="verified", type="string", length=100, nullable=true)
     */
    protected $verified;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=100, nullable=true)
     */
    protected $location;

    /**
     * @var string
     *
     * @ORM\Column(name="location_id", type="string", length=100, nullable=true)
     */
    protected $locationId;

    /**
     * @var string
     *
     * @ORM\Column(name="hometown", type="string", length=100, nullable=true)
     */
    protected $hometown;

    /**
     * @var string
     *
     * @ORM\Column(name="hometown_id", type="string", length=100, nullable=true)
     */
    protected $hometownId;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=20, nullable=true)
     */
    protected $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=50, nullable=true)
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=100, nullable=true)
     */
    protected $timezone;


    /**
     * @var string
     *
     * @ORM\Column(name="nb_friends", type="string", length=15, nullable=true)
     */
    protected $nbFriends;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=100, nullable=true)
     */
    protected $picture;

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
     * Set facebookId
     *
     * @param string $facebookId
     * @return UserFacebook
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return UserFacebook
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }


    /**
     * Set email
     *
     * @param string $email
     * @return UserFacebook
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return UserFacebook
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return UserFacebook
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return UserFacebook
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return UserFacebook
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set verified
     *
     * @param string $verified
     * @return UserFacebook
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Get verified
     *
     * @return string
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return UserFacebook
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set locationId
     *
     * @param string $locationId
     * @return UserFacebook
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;

        return $this;
    }

    /**
     * Get locationId
     *
     * @return string
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Set hometown
     *
     * @param string $hometown
     * @return UserFacebook
     */
    public function setHometown($hometown)
    {
        $this->hometown = $hometown;

        return $this;
    }

    /**
     * Get hometown
     *
     * @return string
     */
    public function getHometown()
    {
        return $this->hometown;
    }

    /**
     * Set hometownId
     *
     * @param string $hometownId
     * @return UserFacebook
     */
    public function setHometownId($hometownId)
    {
        $this->hometownId = $hometownId;

        return $this;
    }

    /**
     * Get hometownId
     *
     * @return string
     */
    public function getHometownId()
    {
        return $this->hometownId;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return UserFacebook
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return UserFacebook
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return UserFacebook
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set $nbFriends
     *
     * @param string $nbFriends
     * @return UserFacebook
     */
    public function setNbFriends($nbFriends)
    {
        $this->nbFriends = $nbFriends;

        return $this;
    }

    /**
     * Get nbFriends
     *
     * @return string
     */
    public function getNbFriends()
    {
        return $this->nbFriends;
    }

    /**
     * Set picture
     *
     * @param string $picture
     * @return UserFacebook
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return UserFacebook
     */
    public function setUser(User $user = null)
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
}

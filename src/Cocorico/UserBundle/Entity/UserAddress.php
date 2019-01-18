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

/**
 * UserAddress
 *
 * @ORM\Entity()
 *
 * @ORM\Table(name="`user_address`", indexes={
 *    @ORM\Index(name="user_address_type_idx", columns={"type"})
 *  })
 *
 */
class UserAddress
{
    use ORMBehaviors\Timestampable\Timestampable;

    const TYPE_BILLING = 1;
    const TYPE_DELIVERY = 2;

    public static $typeValues = array(
        self::TYPE_BILLING => 'entity.user.type.billing',
        self::TYPE_DELIVERY => 'entity.user.type.delivery',
    );

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="addresses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=false, options={"default" : 1})
     */
    protected $type = self::TYPE_BILLING;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=50, nullable=true)
     *
     */
    protected $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=3, nullable=true)
     *
     */
    protected $country = "FR";

    public function __construct()
    {

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
     * Set status
     *
     * @param  integer $type
     * @return $this
     */
    public function setType($type)
    {
        if (!in_array($type, array_keys(self::$typeValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for user_address.type : %s.', $type)
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get type Text
     *
     * @return string
     */
    public function getTypeText()
    {
        return self::$typeValues[$this->getType()];
    }

    /**
     * Set address
     *
     * @param string $address
     * @return UserAddress
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
     * Set city
     *
     * @param string $city
     * @return UserAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return UserAddress
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return UserAddress
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return UserAddress
     */
    public function setUser(User $user)
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

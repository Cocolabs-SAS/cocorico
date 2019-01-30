<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Model;

use Cocorico\UserBundle\Entity\User;

interface UserCardInterface
{
    const VALIDITY_UNKNOWN = 0;
    const VALIDITY_VALID = 1;
    const VALIDITY_INVALID = 2;

    /**
     * @param User $user
     */
    public function setUser(User $user);

    /**
     * @return User
     */
    public function getUser();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getMangopayId();

    /**
     * @param string $mangopayId
     */
    public function setMangopayId($mangopayId);

    /**
     * @return string
     */
    public function getExpirationDate();

    /**
     * @param string $expirationDate
     */
    public function setExpirationDate($expirationDate);

    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @param boolean $active
     */
    public function setActive($active);

    /**
     * @return int
     */
    public function getValidity();

    /**
     * @param int $validity
     */
    public function setValidity($validity);

    /**
     * @return string
     */
    public function getAlias();

    /**
     * @param string $alias
     */
    public function setAlias($alias);

    /**
     * @return string
     */
    public function getCardType();

    /**
     * @param string $cardType
     */
    public function setCardType($cardType);
}
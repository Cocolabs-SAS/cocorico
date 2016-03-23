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

interface ListingAlertInterface
{
    /**
     * @param User $user
     * @return mixed
     */
    public function setUser(User $user);

    /**
     * @return User
     */
    public function getUser();

//    public function getName();

}
<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Event;


class UserEvents
{
    /**
     * The USER_REGISTER event occurs when a new user is registering successfully and before it is persisted.
     *
     * This event allows you to modify user before it is persisted.
     * The event listener method receives a Cocorico\UserBundle\Event\UserEvent instance.
     */
    const USER_REGISTER = 'cocorico.user.register';


    /**
     * The USER_PROFILE_UPDATE event occurs before user profile data are updated.
     *
     * This event allows you to modify user profile data before they are updated.
     * The event listener method receives a Cocorico\UserBundle\Event\UserEvent instance.
     */
    const USER_PROFILE_UPDATE = 'cocorico.user.profile_update';


    /**
     * The USER_BANK_ACCOUNT_UPDATE event occurs before user bank account data are updated.
     *
     * This event allows you to modify user bank account data before they are updated.
     * The event listener method receives a Cocorico\UserBundle\Event\UserEvent instance.
     */
    const USER_BANK_ACCOUNT_UPDATE = 'cocorico.user.bank_account_update';

    /**
     * The USER_PHONE_CHANGE event occurs when user phone change.
     *
     * This event allows you to make action relatively to phone change.
     * The event listener method receives a Cocorico\UserBundle\Event\UserEvent instance.
     */
    const USER_PHONE_CHANGE = 'cocorico.user.phone_change';
}
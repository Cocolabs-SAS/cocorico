<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Event;


class ListingEvents
{
    /**
     * The LISTING_SHOW_QUERY event occurs when a listing is displayed.
     *
     * This event allows you to modify the SQL query.
     * The event listener method receives a Cocorico\CoreBundle\Event\ListingEvent instance.
     */
    const LISTING_SHOW_QUERY = 'cocorico.listing_show.query';

}
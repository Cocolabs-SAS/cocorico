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


class ListingSearchEvents
{
    /**
     * The LISTING_SEARCH event occurs when a new listing search is done.
     *
     * This event allows you to modify the search query.
     * The event listener method receives a Cocorico\CoreBundle\Event\ListingSearchEvent instance.
     */
    const LISTING_SEARCH = 'cocorico.listing_search';

}
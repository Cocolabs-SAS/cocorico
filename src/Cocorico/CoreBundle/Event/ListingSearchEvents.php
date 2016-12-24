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
     * The LISTING_SEARCH event occurs when the listing search query is build.
     *
     * This event allows you to modify the search query.
     * The event listener method receives a Cocorico\CoreBundle\Event\ListingSearchEvent instance.
     *
     * todo: Renamed to LISTING_SEARCH_QUERY
     */
    const LISTING_SEARCH = 'cocorico.listing_search';

    /**
     * The LISTING_SEARCH_ACTION event occurs when the listing search action is called.
     *
     * This event allows you to add params to pass to the view rendered by the listing search action.
     * The event listener method receives a Cocorico\CoreBundle\Event\ListingSearchActionEvent instance.
     */
    const LISTING_SEARCH_ACTION = 'cocorico.listing_search.action';
}
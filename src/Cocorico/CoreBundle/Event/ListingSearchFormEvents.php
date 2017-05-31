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


class ListingSearchFormEvents
{
    /**
     * The LISTING_SEARCH_RESULT_FORM_BUILD event is thrown each time listing search result form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\ListingFormBuilderEvent instance.
     */
    const LISTING_SEARCH_RESULT_FORM_BUILD = 'cocorico.listing_search.result.form.build';
}
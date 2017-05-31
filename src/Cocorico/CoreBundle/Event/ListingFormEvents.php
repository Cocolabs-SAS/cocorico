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


class ListingFormEvents
{
    /**
     * The LISTING_EDIT_CATEGORIES_FORM_BUILD event is thrown each time listing categories edition form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\ListingFormBuilderEvent instance.
     */
    const LISTING_EDIT_CATEGORIES_FORM_BUILD = 'cocorico.listing_edit.categories.form.build';

    /**
     * The LISTING_NEW_CATEGORIES_FORM_BUILD event is thrown each time listing categories creation form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\ListingFormBuilderEvent instance.
     */
    const LISTING_NEW_CATEGORIES_FORM_BUILD = 'cocorico.listing_new.categories.form.build';

    /**
     * The LISTING_EDIT_PRICE_FORM_BUILD event is thrown each time listing edit price form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\ListingFormBuilderEvent instance.
     */
    const LISTING_EDIT_PRICE_FORM_BUILD = 'cocorico.listing_edit.price.form.build';


    /**
     * The LISTING_NEW_FORM_BUILD event is thrown each time listing new form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\ListingFormBuilderEvent instance.
     */
    const LISTING_NEW_FORM_BUILD = 'cocorico.listing_new.form.build';

}
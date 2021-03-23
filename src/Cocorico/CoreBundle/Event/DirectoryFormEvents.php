<?php
namespace Cocorico\CoreBundle\Event;


class DirectoryFormEvents
{
    /**
     * The DIRECTORY_EDIT_CATEGORIES_FORM_BUILD event is thrown each time listing categories edition form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\DirectoryFormBuilderEvent instance.
     */
    const DIRECTORY_EDIT_CATEGORIES_FORM_BUILD = 'cocorico.listing_edit.categories.form.build';

    /**
     * The DIRECTORY_NEW_CATEGORIES_FORM_BUILD event is thrown each time listing categories creation form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\DirectoryFormBuilderEvent instance.
     */
    const DIRECTORY_NEW_CATEGORIES_FORM_BUILD = 'cocorico.listing_new.categories.form.build';

    /**
     * The DIRECTORY_NEW_FORM_BUILD event is thrown each time listing new form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\DirectoryFormBuilderEvent instance.
     */
    const DIRECTORY_NEW_FORM_BUILD = 'cocorico.listing_new.form.build';

}

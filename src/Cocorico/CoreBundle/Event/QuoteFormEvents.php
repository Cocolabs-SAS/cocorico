<?php


namespace Cocorico\CoreBundle\Event;


class QuoteFormEvents
{
    /**
     * The QUOTE_NEW_FORM_BUILD event is thrown each time a new quote form is build
     *
     * This event allows you to add form fields and validation on them.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\QuoteFormBuilderEvent instance.
     */
    const QUOTE_NEW_FORM_BUILD = 'cocorico.quote_new.form.build';


    /**
     * The QUOTE_NEW_FORM_PROCESS event is thrown each time a new quote form is processed
     *
     * This event allows you to process and validate extra form fields.
     *
     * The event listener receives a \Cocorico\CoreBundle\Event\QuoteFormEvent instance.
     */
    const QUOTE_NEW_FORM_PROCESS = 'cocorico.quote_new.form.process';
}

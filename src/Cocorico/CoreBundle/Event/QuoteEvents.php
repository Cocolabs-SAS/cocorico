<?php

namespace Cocorico\CoreBundle\Event;


class QuoteEvents
{
    /**
     * The QUOTE_INIT event occurs when a quote quote is initialized.
     *
     * This event allows you to set the response instead of using the default one.
     * The event listener method receives a Cocorico\CoreBundle\Event\QuoteEvent instance.
     */
    const QUOTE_INIT = 'cocorico.quote_new.init';

    /**
     * The QUOTE_NEW_SUBMITTED event occurs when the new quote form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     * The event listener method receives a Cocorico\CoreBundle\Event\QuoteEvent instance.
     */
    const QUOTE_NEW_SUBMITTED = 'cocorico.quote_new.submitted';

    /**
     * The QUOTE_NEW_CREATED event occurs after new quote has been created with status new.
     *
     * This event allows you to do things after a new quote has been successfully created.
     * The event listener method receives a Cocorico\CoreBundle\Event\QuoteEvent instance.
     */
    const QUOTE_NEW_CREATED = 'cocorico.quote_new.created';


    /**
     * The QUOTE_VALIDATE event occurs when a quote is considered as done (started or finished).
     *
     * This event allows you to do what you want when the quote is considered as done (offerer payment, ...).
     * The event listener method receives a Cocorico\CoreBundle\Event\QuoteValidateEvent instance.
     */
    const QUOTE_VALIDATE = 'cocorico.quote.validate';

}

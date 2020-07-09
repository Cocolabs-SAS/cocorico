<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Event;


use Cocorico\CoreBundle\Event\QuoteEvent;
use Cocorico\CoreBundle\Event\QuoteEvents;
use Cocorico\MessageBundle\Model\ThreadManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class QuoteSubscriber implements EventSubscriberInterface
{
    protected $threadManager;

    /**
     * @param ThreadManager $threadManager
     */
    public function __construct(ThreadManager $threadManager)
    {
        $this->threadManager = $threadManager;
    }


    public function onQuoteNewCreated(QuoteEvent $event)
    {
        $quote = $event->getQuote();
        $user = $quote->getUser();
        $this->threadManager->createNewQuoteListingThread($user, $quote);
    }


    public static function getSubscribedEvents()
    {
        return array(
            QuoteEvents::QUOTE_NEW_CREATED => array('onQuoteNewCreated', 1),
        );
    }

}

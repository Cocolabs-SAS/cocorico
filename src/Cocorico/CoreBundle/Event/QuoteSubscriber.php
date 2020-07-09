<?php

namespace Cocorico\CoreBundle\Event;

use Cocorico\CoreBundle\Model\Manager\QuoteManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class QuoteSubscriber implements EventSubscriberInterface
{
    protected $quoteManager;
    protected $dispatcher;

    /**
     * @param QuoteManager           $quoteManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(QuoteManager $quoteManager, EventDispatcherInterface $dispatcher)
    {
        $this->quoteManager = $quoteManager;
        $this->dispatcher = $dispatcher;
    }


    /**
     * Create a new quote
     *
     * @param QuoteEvent $event
     */
    public function onQuoteNewSubmitted(QuoteEvent $event)
    {
        $quote = $this->quoteManager->create($event->getQuote());
        if ($quote) {
            $event->setQuote($quote);
            $this->dispatcher->dispatch(QuoteEvents::QUOTE_NEW_CREATED, $event);
        }
    }


    public static function getSubscribedEvents()
    {
        return array(
            QuoteEvents::QUOTE_NEW_SUBMITTED => array('onQuoteNewSubmitted', 0)
        );
    }

}

<?php


namespace Cocorico\CoreBundle\Model;


use Cocorico\CoreBundle\Entity\Quote;

interface QuoteOptionInterface
{
    /**
     * @param Quote $quote
     * @return mixed
     */
    public function setQuote(Quote $quote);

    /**
     * @return Quote
     */
    public function getQuote();
}

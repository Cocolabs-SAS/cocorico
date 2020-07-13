<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Form\Handler\Dashboard;

use Cocorico\CoreBundle\Entity\Quote;
use Symfony\Component\Form\Form;

/**
 * Handle Asker Dashboard Quote Form
 *
 */
class QuoteAskerFormHandler extends QuoteFormHandler
{
    /**
     * Save Quote.
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -2: Wrong Quote Status
     * -3: Refund error
     * -4: Unknown error
     */
    protected function onSuccess(Form $form)
    {
        $result = -4; //Unknown error

        /** @var Quote $quote */
        $quote = $form->getData();
        $message = $form->get("message")->getData();
        $this->threadManager->addReplyQuoteThread($quote, $message, $quote->getUser());
        //Cancel
        $type = $this->request->get('type');
        if (in_array($quote->getStatus(), Quote::$cancelableStatus)) {
            if ($type == 'cancel') {
                if ($this->quoteManager->cancel($quote)) {
                    $result = 1;
                } 
            }
        } 

        if ($this->quoteManager->canBeAcceptedByAsker($quote) or
            $this->quoteManager->canBeRefusedByAsker($quote)) {
            if ($type == 'accept') {
                $this->quoteManager->accept($quote);
                $result = 1;
            }

            if ($type == 'refuse') {
                $this->quoteManager->refuse($quote, false);
                $result = 1;
            }
        } else {
            $result = -2;
        }

        return $result;
    }
}

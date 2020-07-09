<?php

namespace Cocorico\CoreBundle\Form\Handler\Dashboard;

use Cocorico\CoreBundle\Entity\Quote;
use Symfony\Component\Form\Form;

/**
 * Handle Offerer Dashboard Quote Form
 *
 */
class QuoteOffererFormHandler extends QuoteFormHandler
{
    /**
     * Save Quote.
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -2: Wrong Quote Status
     * -3: Payin PreAuth error
     * -4: Unknown error
     */
    protected function onSuccess(Form $form)
    {
        $result = -4; //Unknown error

        /** @var Quote $quote */
        $quote = $form->getData();
        $message = $form->get("message")->getData();
        $this->threadManager->addReplyQuoteThread($quote, $message, $quote->getListing()->getUser());
        //Accept or refuse
        $type = $this->request->get('type');

        $canBeAcceptedOrRefused = $this->quoteManager->canBeAcceptedOrRefusedByOfferer($quote);
        if ($canBeAcceptedOrRefused) {
            if ($type == 'accept') {
                if ($this->quoteManager->pay($quote)) {
                    $result = 1;
                } else {
                    $result = -3;
                }
            } elseif ($type == 'refuse') {
                $this->quoteManager->refuse($quote);
                $result = 1;
            }
        } else {
            $result = -2;
        }

        return $result;
    }
}

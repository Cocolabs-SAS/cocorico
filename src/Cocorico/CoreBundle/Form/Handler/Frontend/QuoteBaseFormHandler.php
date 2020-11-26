<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Event\QuoteEvent;
use Cocorico\CoreBundle\Event\QuoteEvents;
use Cocorico\CoreBundle\Event\QuoteFormEvent;
use Cocorico\CoreBundle\Event\QuoteFormEvents;
use Cocorico\CoreBundle\Model\Manager\QuoteManager;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Quote Form
 *
 */
class QuoteBaseFormHandler
{
    protected $request;
    protected $flashBag;
    protected $quoteManager;
    protected $dispatcher;

    /**
     * @param RequestStack             $requestStack
     * @param QuoteManager           $quoteManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RequestStack $requestStack,
        QuoteManager $quoteManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->quoteManager = $quoteManager;
        $this->dispatcher = $dispatcher;
    }


    /**
     * Init quote
     *
     * @param User|null $user
     * @param Listing   $listing
     * @param string $communication
     * @param intr $budget
     * @param \DateTime $prestaStartDate format yyyy-mm-dd-H:i
     *
     * @return Quote $quote
     */
    public function init(
        $user,
        Listing $listing,
        string $communication = Null,
        int $budget = Null,
        \DateTime $prestaStartDate = Null
    ) {
        //Id of an eventual quote draft
        $quoteId = $this->request->query->get('id');
        //If no quote draft exists a new quote is initialized
        if (!$quoteId) {
            //Deduct time range from date range
            $quote = $this->quoteManager->initQuote($listing, $user);
            $quote->setBudget($budget);
            $quote->setCommunication($communication);
            $quote->setPrestaStartDate($prestaStartDate);

            $event = new QuoteEvent($quote);
            $this->dispatcher->dispatch(QuoteEvents::QUOTE_INIT, $event);
            $quote = $event->getQuote();
        } else {
            //If quote draft exists it is returned
            $quote = $this->quoteManager->getRepository()->findOneBy(
                array(
                    'id' => $quoteId,
                    'status' => Quote::STATUS_DRAFT,
                    'user' => $user->getId()
                )
            );
        }

        return $quote;
    }

    /**
     * Process form
     *
     * @param $form
     *
     * @return int equal to :
     * 4: Options success
     * 3: Delivery success
     * 2: Voucher code success
     * 1: Success
     * 0: if form is not submitted:
     * -1: if form is not valid
     * -2: Self quote error
     * -3: Voucher error on code
     * -4: Voucher error on quote amount
     * -5: the max delivery distance has been reached
     * -6: distance matrix api error
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST')) {
            if (count($this->dispatcher->getListeners(QuoteFormEvents::QUOTE_NEW_FORM_PROCESS)) > 0) {
                try {
                    $event = new QuoteFormEvent($form);
                    $this->dispatcher->dispatch(QuoteFormEvents::QUOTE_NEW_FORM_PROCESS, $event);
                    $result = $event->getResult();
                    if ($result !== false) {
                        return $result;
                    }
                } catch (\Exception $e) {

                }
            }

            if ($form->isValid()) {
                $result = $this->onSuccess($form);
            } else {
                $result = -1;//form not valid
            }
        } else {
            $result = 0; //Not submitted
        }

        return $result;
    }

    /**
     * notify regular quote
     *
     * @param Listing       $listing
     * @return void
     */
    public function notifyRegularQuote(Quote $quote)
    {
        $this->QuoteManager->notifyQuote('ask-demand');
        $this->QuoteManager->notifyQuote('off-notif');

    }

    /**
     * notif flash quote
     *
     * @param Listing       $listing
     * @return void
     */
    public function notifyFlashQuote(Quote $quote)
    {
        $this->QuoteManager->notifyQuote($quote, 'ask-flash-demand');
        $this->QuoteManager->notifyQuote($quote, 'off-notif');
    }

    /**
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -2: Self quote error
     */
    private function onSuccess(Form $form)
    {
        /** @var Quote $quote */
        $quote = $form->getData();

        //No self quote
        if ($quote->getUser() == $quote->getListing()->getUser()) {
            $result = -2;

            return $result;
        }

        return 1;
    }

}

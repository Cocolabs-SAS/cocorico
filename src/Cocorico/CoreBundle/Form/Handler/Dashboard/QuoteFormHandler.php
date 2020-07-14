<?php

namespace Cocorico\CoreBundle\Form\Handler\Dashboard;

use Cocorico\CoreBundle\Model\Manager\QuoteManager;
use Cocorico\MessageBundle\Model\ThreadManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Quote Form
 *
 */
abstract class QuoteFormHandler
{
    /** @var Request $request */
    protected $request;
    /** @var QuoteManager $quoteManager */
    protected $quoteManager;
    /** @var  ThreadManager $threadManager */
    protected $threadManager;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();;
    }

    /**
     * @param QuoteManager $quoteManager
     */
    public function setQuoteManager(QuoteManager $quoteManager)
    {
        $this->quoteManager = $quoteManager;
    }

    /**
     * @param ThreadManager $threadManager
     */
    public function setThreadManager(ThreadManager $threadManager)
    {
        $this->threadManager = $threadManager;
    }

    /**
     * Process form
     *
     * @param $form
     *
     * @return int equal to :
     * 1: Success
     * 0: if form is not submitted:
     * -1: if form is not valid
     * -2: Wrong Quote Status
     *
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST')) {
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
     * To override
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -2:
     * -3:
     * -4: Unknown error
     */
    abstract protected function onSuccess(Form $form);


}

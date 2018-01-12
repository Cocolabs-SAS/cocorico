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

use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Cocorico\MessageBundle\Model\ThreadManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Booking Form
 *
 */
abstract class BookingFormHandler
{
    /** @var Request $request */
    protected $request;
    /** @var BookingManager $bookingManager */
    protected $bookingManager;
    /** @var  ThreadManager $threadManager */
    protected $threadManager;
    /** @var  int */
    protected $expirationDelay;
    /** @var  int */
    protected $acceptationDelay;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();;
    }

    /**
     * @param BookingManager $bookingManager
     */
    public function setBookingManager(BookingManager $bookingManager)
    {
        $this->bookingManager = $bookingManager;
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
     * -2: Wrong Booking Status
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
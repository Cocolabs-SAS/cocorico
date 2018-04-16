<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Form\Handler;

use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Cocorico\UserBundle\Model\UserManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

class BankAccountFormHandler
{
    protected $request;
    protected $userManager;
    protected $dispatcher;

    /**
     * @param RequestStack             $requestStack
     * @param UserManager              $userManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RequestStack $requestStack,
        UserManager $userManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Form $form
     * @return int equal to :
     * 1: Success
     * 0: if form is not submitted:
     * -1: if form is not valid
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
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -1: if error while listening USER_BANK_ACCOUNT_UPDATE event
     */
    protected function onSuccess($form)
    {
        $result = 1;

        $user = $form->getData();

        try {
            $event = new UserEvent($user);
            $this->dispatcher->dispatch(UserEvents::USER_BANK_ACCOUNT_UPDATE, $event);
            $user = $event->getUser();
        } catch (\Exception $e) {
            $result = -1;
        }

        $this->userManager->updateUser($user);

        return $result;
    }


}

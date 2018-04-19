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

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Entity\UserAddress;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Cocorico\UserBundle\Model\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactFormHandler
{
    protected $request;
    protected $userManager;
    protected $dispatcher;
    protected $addressDelivery;

    /**
     * @param RequestStack             $requestStack
     * @param UserManager              $userManager
     * @param EventDispatcherInterface $dispatcher
     * @param bool                     $addressDelivery
     */
    public function __construct(
        RequestStack $requestStack,
        UserManager $userManager,
        EventDispatcherInterface $dispatcher,
        $addressDelivery
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
        $this->dispatcher = $dispatcher;
        $this->addressDelivery = $addressDelivery;
    }

    /**
     * @param UserInterface $user
     * @return User
     */
    public function init(UserInterface $user)
    {
        /** @var User $user */
        if (!$user->getAddressesOfType(UserAddress::TYPE_BILLING)->count()) {
            $address = new UserAddress();
            $address->setType(UserAddress::TYPE_BILLING);
            $address->setUser($user);
            $user->addAddress($address);
        }

        if ($this->addressDelivery && !$user->getAddressesOfType(UserAddress::TYPE_DELIVERY)->count()) {
            $address = new UserAddress();
            $address->setType(UserAddress::TYPE_DELIVERY);
            $address->setUser($user);
            $user->addAddress($address);
        }

        return $user;
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
            $this->dispatcher->dispatch(UserEvents::USER_PROFILE_UPDATE, $event);
            $user = $event->getUser();
        } catch (\Exception $e) {
            $result = -1;
        }

        $this->userManager->updateUser($user);

        return $result;
    }


}

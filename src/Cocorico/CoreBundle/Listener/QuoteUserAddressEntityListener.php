<?php


namespace Cocorico\CoreBundle\Listener;

use Cocorico\CoreBundle\Entity\quoteUserAddress;
use Cocorico\UserBundle\Entity\UserAddress;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Doctrine ORM listener for quote user address
 *
 */
class QuoteUserAddressEntityListener implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handleEvent($args);
    }


    private function handleEvent(LifecycleEventArgs $args)
    {
        /** @var quoteUserAddress $address */
        $address = $args->getEntity();
        if ($address instanceof quoteUserAddress) {
            $this->handlequoteUserAddressPostPersistEvent($address, $args);
        }
    }

    /**
     * Set user addresses equal to the last quote user address
     *
     * @param quoteUserAddress $address
     * @param LifecycleEventArgs $args
     */
    private function handlequoteUserAddressPostPersistEvent(quoteUserAddress $address, LifecycleEventArgs $args)
    {
        $this->setUserAddressDelivery($address, $args);
    }

    /**
     * @param quoteUserAddress $address
     * @param LifecycleEventArgs $args
     */
    private function setUserAddressDelivery(quoteUserAddress $address, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $user = $address->getquote()->getUser();

        //Add or update user address
        $userAddresses = $user->getAddressesOfType(UserAddress::TYPE_DELIVERY);
        if ($userAddresses->count()) {
            /** @var UserAddress $userAddress */
            $userAddress = $userAddresses->first();
        } else {
            $userAddress = new UserAddress();
            $userAddress->setType(UserAddress::TYPE_DELIVERY);
            $userAddress->setUser($user);
        }
        $userAddress->setAddress($address->getAddress());
        $userAddress->setCity($address->getCity());
        $userAddress->setZip($address->getZip());
        $userAddress->setCountry($address->getCountry());

        $em->persist($userAddress);
        $em->flush($userAddress);

    }

}

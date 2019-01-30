<?php


namespace Cocorico\CoreBundle\Listener;

use Cocorico\CoreBundle\Entity\BookingUserAddress;
use Cocorico\UserBundle\Entity\UserAddress;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Doctrine ORM listener for booking user address
 *
 */
class BookingUserAddressEntityListener implements EventSubscriber
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
        /** @var BookingUserAddress $address */
        $address = $args->getEntity();
        if ($address instanceof BookingUserAddress) {
            $this->handleBookingUserAddressPostPersistEvent($address, $args);
        }
    }

    /**
     * Set user addresses equal to the last booking user address
     *
     * @param BookingUserAddress $address
     * @param LifecycleEventArgs $args
     */
    private function handleBookingUserAddressPostPersistEvent(BookingUserAddress $address, LifecycleEventArgs $args)
    {
        $this->setUserAddressDelivery($address, $args);
        $this->setUserAddressBilling($address, $args);
    }

    /**
     * @param BookingUserAddress $address
     * @param LifecycleEventArgs $args
     */
    private function setUserAddressDelivery(BookingUserAddress $address, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $user = $address->getBooking()->getUser();

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


    /**
     * Set user billing address equal to last booking user delivery address if user doesn't have billing address
     *
     * @param BookingUserAddress $address
     * @param LifecycleEventArgs $args
     */
    private function setUserAddressBilling(BookingUserAddress $address, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $user = $address->getBooking()->getUser();

        //Add or update user address
        $userAddresses = $user->getAddressesOfType(UserAddress::TYPE_BILLING);
        if (!$userAddresses->count()) {
            $userAddress = new UserAddress();
            $userAddress->setType(UserAddress::TYPE_BILLING);
            $userAddress->setUser($user);
            $userAddress->setAddress($address->getAddress());
            $userAddress->setCity($address->getCity());
            $userAddress->setZip($address->getZip());
            $userAddress->setCountry($address->getCountry());

            $em->persist($userAddress);
            $em->flush($userAddress);
        }
    }
}

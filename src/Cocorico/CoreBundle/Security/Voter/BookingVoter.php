<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BookingVoter implements VoterInterface
{
    const VIEW_AS_ASKER = 'view_as_asker';
    const VIEW_AS_OFFERER = 'view_as_offerer';
    const EDIT_AS_OFFERER = 'edit_as_offerer';
    const EDIT_AS_ASKER = 'edit_as_asker';
    const VIEW_VOUCHER_AS_ASKER = 'view_voucher_as_asker';

    public function supportsAttribute($attribute)
    {
        return in_array(
            $attribute,
            array(
                self::VIEW_AS_ASKER,
                self::VIEW_AS_OFFERER,
                self::EDIT_AS_OFFERER,
                self::EDIT_AS_ASKER,
                self::VIEW_VOUCHER_AS_ASKER,
            )
        );
    }

    public function supportsClass($class)
    {
        $supportedClass = 'Cocorico\CoreBundle\Entity\Booking';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    /**
     * @param  TokenInterface $token
     * @param  null|Booking   $booking
     * @param  array          $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $booking, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($booking))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the voter is used correct, only allow one attribute
        // this isn't a requirement, it's just one easy way for you to
        // design your voter
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW_AS_ASKER or VIEW_AS_OFFERER or EDIT_AS_OFFERER or EDIT_AS_ASKER or VIEW_VOUCHER_AS_ASKER'
            );
        }

        // set the attribute to check against
        $attribute = $attributes[0];

        // check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // get current logged in user
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) { // || $user->hasRole('ROLE_SUPER_ADMIN'
            return VoterInterface::ACCESS_DENIED;
        }

        $listing = $booking->getListing();
        $offerer = $listing->getUser();
        $asker = $booking->getUser();

        if ($attribute == self::VIEW_AS_ASKER || $attribute == self::EDIT_AS_ASKER) {
            if ($user->getId() === $asker->getId()) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif ($attribute == self::VIEW_VOUCHER_AS_ASKER) {
            if ($user->getId() === $asker->getId() && in_array($booking->getStatus(), Booking::$payedStatus)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif ($attribute == self::VIEW_AS_OFFERER || $attribute == self::EDIT_AS_OFFERER) {
            if ($user->getId() === $offerer->getId()) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } else {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}

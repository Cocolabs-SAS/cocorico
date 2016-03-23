<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ReviewBundle\Security\Voter;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReviewVoter implements VoterInterface
{
    const ADD = 'add';

    public function supportsAttribute($attribute)
    {
        return in_array(
            $attribute,
            array(
                self::ADD,
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
                'Only one attribute is allowed for ADD review'
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

        switch ($attribute) {
            case self::ADD:
                // make sure there is a user object (i.e. that the user is logged in)
                if (!$user instanceof UserInterface) {
                    return VoterInterface::ACCESS_DENIED;
                }
                if ($user->getId() === $booking->getUser()->getId() ||
                    $user->getId() === $booking->getListing()->getUser()->getId()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}

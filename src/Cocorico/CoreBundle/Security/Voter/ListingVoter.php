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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ListingVoter implements VoterInterface
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const BOOKING = 'booking';//For new booking

    public function supportsAttribute($attribute)
    {
        return in_array(
            $attribute,
            array(
                self::VIEW,
                self::EDIT,
                self::BOOKING,
            )
        );
    }

    public function supportsClass($class)
    {
        $supportedClass = 'Cocorico\CoreBundle\Entity\Listing';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    /**
     * @param  TokenInterface $token
     * @param  null|Listing   $listing
     * @param  array          $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $listing, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($listing))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the voter is used correct, only allow one attribute
        // this isn't a requirement, it's just one easy way for you to
        // design your voter
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW or EDIT'
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
            case self::BOOKING:
            case self::VIEW:
                if ($listing->getStatus() == Listing::STATUS_PUBLISHED
                    || (
                        $user instanceof UserInterface &&
                        $user->getId() === $listing->getUser()->getId() &&
                        $listing->getStatus() != Listing::STATUS_DELETED
                    )
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            case self::EDIT:
                // make sure there is a user object (i.e. that the user is logged in)
                if (!$user instanceof UserInterface) {
                    return VoterInterface::ACCESS_DENIED;
                }
                if ($user->getId() === $listing->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}

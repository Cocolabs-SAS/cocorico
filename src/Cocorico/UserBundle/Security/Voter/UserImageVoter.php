<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Security\Voter;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Entity\UserImage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserImageVoter implements VoterInterface
{
    const VIEW = 'view';
    const EDIT = 'edit';

    public function supportsAttribute($attribute)
    {
        return in_array(
            $attribute,
            array(
                self::VIEW,
                self::EDIT,
            )
        );
    }

    public function supportsClass($class)
    {
        $supportedClass = 'Cocorico\UserBundle\Entity\UserImage';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    /**
     * @param  TokenInterface $token
     * @param  null|UserImage $userImage
     * @param  array          $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $userImage, array $attributes)
    {
        if (!$this->supportsClass(get_class($userImage))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW or EDIT'
            );
        }

        $attribute = $attributes[0];

        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        /** @var User $user */
        $user = $token->getUser();

        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        switch ($attribute) {
            case self::VIEW:
                return VoterInterface::ACCESS_GRANTED;
                break;

            case self::EDIT:
                if ($user->getId() === $userImage->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}

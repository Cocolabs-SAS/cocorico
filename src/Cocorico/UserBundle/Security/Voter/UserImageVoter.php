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

use Cocorico\UserBundle\Entity\UserImage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserImageVoter extends Voter
{
    const EDIT = 'edit';
    const VIEW = 'view';

    const ATTRIBUTES = [
        self::EDIT,
        self::VIEW,
    ];

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return boolean
     */
    public function supports($attribute, $subject)
    {
        return ($subject instanceof UserImage) && in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // make sure there is a user object (i.e. that the user is logged in)
        if (!$token->getUser() instanceof UserInterface) {
            return Voter::ACCESS_DENIED;
        }

        $method = 'voteOn' . str_replace('_', '', ucwords($attribute, '_'));
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException('Expected method ' . $method . ' was not found.');
        }

        return $this->{$method}($subject, $token);
    }

    /**
     * @param UserImage $userImage
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnView(UserImage $userImage, TokenInterface $token)
    {
        return true;
    }

    /**
     * @param UserImage $userImage
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnEdit(UserImage $userImage, TokenInterface $token)
    {
        return ($token->getUser()->getId() === $userImage->getUser()->getId());
    }
}

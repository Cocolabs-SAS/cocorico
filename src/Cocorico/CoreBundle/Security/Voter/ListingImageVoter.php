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

use Cocorico\CoreBundle\Entity\ListingImage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ListingImageVoter extends Voter
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
        return ($subject instanceof ListingImage) && in_array($attribute, self::ATTRIBUTES);
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
     * @param ListingImage $listingImage
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnView(ListingImage $listingImage, TokenInterface $token)
    {
        return Voter::ACCESS_GRANTED;
    }

    /**
     * @param ListingImage $listingImage
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnEdit(ListingImage $listingImage, TokenInterface $token)
    {
        return ($token->getUser()->getId() === $listingImage->getListing()->getUser()->getId());
    }
}

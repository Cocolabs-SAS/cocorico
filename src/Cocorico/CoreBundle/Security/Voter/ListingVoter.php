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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ListingVoter extends Voter
{
    const BOOKING = 'booking';
    const EDIT = 'edit';
    const VIEW = 'view';

    const ATTRIBUTES = [
        self::BOOKING,
        self::VIEW,
        self::EDIT,
    ];

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return boolean
     */
    public function supports($attribute, $subject)
    {
        return ($subject instanceof Listing) && in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return integer
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
     * @param Listing $listing
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnBooking(Listing $listing, TokenInterface $token)
    {
        return (
            $listing->getStatus() == Listing::STATUS_PUBLISHED
            || (
                $token->getUser()->getId() === $listing->getUser()->getId()
                && $listing->getStatus() != Listing::STATUS_DELETED
            )
        );
    }

    /**
     * @param Listing $listing
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnEdit(Listing $listing, TokenInterface $token)
    {
        return ($token->getUser()->getId() === $listing->getUser()->getId());
    }

    /**
     * @param Listing $listing
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnView(Listing $listing, TokenInterface $token)
    {
        return (
            $listing->getStatus() == Listing::STATUS_PUBLISHED
            || (
                $token->getUser()->getId() === $listing->getUser()->getId()
                && $listing->getStatus() != Listing::STATUS_DELETED
            )
        );
    }
}

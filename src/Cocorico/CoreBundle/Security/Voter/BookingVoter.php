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
use Cocorico\CoreBundle\Entity\Listing;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BookingVoter extends Voter
{
    const CREATE = 'create';
    const EDIT_AS_ASKER = 'edit_as_asker';
    const EDIT_AS_OFFERER = 'edit_as_offerer';
    const VIEW_AS_ASKER = 'view_as_asker';
    const VIEW_AS_OFFERER = 'view_as_offerer';
    const VIEW_VOUCHER_AS_ASKER = 'view_voucher_as_asker';

    const ATTRIBUTES = [
        self::CREATE,
        self::EDIT_AS_OFFERER,
        self::EDIT_AS_ASKER,
        self::VIEW_AS_ASKER,
        self::VIEW_AS_OFFERER,
        self::VIEW_VOUCHER_AS_ASKER,
    ];

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return boolean
     */
    public function supports($attribute, $subject)
    {
        return ($subject instanceof Booking) && in_array($attribute, self::ATTRIBUTES);
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
     * @param Booking $booking
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnCreate(Booking $booking, TokenInterface $token)
    {
        return (
            $token->getUser()->getId() === $booking->getUser()->getId()
            && $booking->getListing()->getStatus() == Listing::STATUS_PUBLISHED
            && in_array($booking->getStatus(), Booking::$newableStatus)
        );
    }

    /**
     * @param Booking $booking
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnViewAsAsker(Booking $booking, TokenInterface $token)
    {
        return ($token->getUser()->getId() === $booking->getUser()->getId());
    }

    /**
     * @param Booking $booking
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnEditAsAsker(Booking $booking, TokenInterface $token)
    {
        return ($token->getUser()->getId() === $booking->getUser()->getId());
    }

    /**
     * @param Booking $booking
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnViewVoucherAsAsker(Booking $booking, TokenInterface $token)
    {
        return (
            $token->getUser()->getId() === $booking->getUser()->getId()
            && in_array($booking->getStatus(), Booking::$payedStatus)
        );
    }

    /**
     * @param Booking $booking
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnViewAsOfferer(Booking $booking, TokenInterface $token)
    {
        return ($token->getUser()->getId() === $booking->getListing()->getUser()->getId());
    }

    /**
     * @param Booking $booking
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnEditAsOfferer(Booking $booking, TokenInterface $token)
    {
        return ($token->getUser()->getId() === $booking->getListing()->getUser()->getId());
    }
}

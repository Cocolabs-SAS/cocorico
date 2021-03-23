<?php

/*
 * FIXME: Needs improvements
 */

namespace Cocorico\CoreBundle\Security\Voter;

use Cocorico\CoreBundle\Entity\Directory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class DirectoryVoter extends Voter
{
    const ADOPT = 'adopt';
    const EDIT = 'edit';
    const VIEW = 'view';

    const ATTRIBUTES = [
        self::ADOPT,
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
        return ($subject instanceof Directory) && in_array($attribute, self::ATTRIBUTES);
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
     * @param Directory $directory
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnAdopt(Directory $directory, TokenInterface $token)
    {
        // Can only adopt if user is null
        return $directory->getUser() == null
        // return (
        //     $token->getUser()->getId() === $directory->getUser()->getId()
        //     && $directory->getListing()->getStatus() == Listing::STATUS_PUBLISHED
        //     && in_array($directory->getStatus(), Directory::$newableStatus)
        // );
    }

    /**
     * @param Directory $directory
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnEdit(Directory $directory, TokenInterface $token)
    {
        // FIXME
        return true;
        // return ($token->getUser()->getId() === $directory->getUser()->getId());
    }

    /**
     * @param Directory $directory
     * @param TokenInterface $token
     *
     * @return boolean
     */
    protected function voteOnView(Directory $directory, TokenInterface $token)
    {
        // FIXME
        return true;
        // return ($token->getUser()->getId() === $directory->getUser()->getId());
    }

}

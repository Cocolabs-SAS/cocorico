<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\ReviewBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\ReviewBundle\Entity\Review;
use Cocorico\ReviewBundle\Model\ReviewManager;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Review Form
 */
class ReviewFormHandler
{
    protected $request;

    protected $reviewManager;

    /**
     * @param RequestStack  $requestStack
     * @param ReviewManager $reviewManager
     */
    public function __construct(RequestStack $requestStack, ReviewManager $reviewManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->reviewManager = $reviewManager;
    }

    /**
     * create Review Object with basic details
     *
     * @param Booking $booking
     * @param User    $user
     *
     * @return Review|boolean
     */
    public function create(Booking $booking, User $user)
    {
        $review = null;
        if ($booking->isValidated() && !$this->reviewManager->userHasReviewed($booking, $user)) {
            $review = new Review();
            $review->setBooking($booking);
            $review->setReviewBy($user);
            // sets the correct user for review to depending upon the reviewer
            if ($booking->getUser()->getId() === $user->getId()) {
                $reviewTo = $booking->getListing()->getUser();
            } else {
                $reviewTo = $booking->getUser();
            }
            $review->setReviewTo($reviewTo);
        }

        return $review;
    }

    /**
     * Process form
     *
     * @param Form $form
     *
     * @return Review|boolean
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST') && $form->isValid()) {
            return $this->onSuccess($form);
        }

        return false;
    }

    /**
     * @param Form $form
     * @return Review
     */
    private function onSuccess(Form $form)
    {
        /** @var Review $review */
        $review = $form->getData();

        return $this->reviewManager->save($review);
    }
}

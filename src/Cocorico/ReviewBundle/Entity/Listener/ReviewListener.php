<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\ReviewBundle\Entity\Listener;

use Cocorico\ReviewBundle\Entity\Review;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Review Listener will listen the post persist event on the review entity, and calculate
 * and update the reviews of the users as asker & offerer, while it also calculates the
 * ratings of the listing and stores to their relative entity
 */
class ReviewListener
{
    /**
     * postPersist will handle the post persist event for Review entity
     *
     * @param  Review             $review
     * @param  LifecycleEventArgs $args
     * @return void
     */
    public function postPersist(Review $review, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        // update user ratings
        $userReviews = $em->getRepository('CocoricoReviewBundle:Review')->getUserAverage($review);

        // update listing ratings
        $listingReviews = $em->getRepository('CocoricoReviewBundle:Review')->getListingAverage($review);

        $listing = $review->getBooking()->getListing();
        $user = $review->getReviewTo();
        // update asker ratings
        if ($userReviews['asker_avg'] > 0) {
            $user->setAverageAskerRating(ceil($userReviews['asker_avg']));
        }
        // update offerer ratings
        if ($userReviews['offerer_avg'] > 0) {
            $user->setAverageOffererRating(ceil($userReviews['offerer_avg']));
        }
        // persist user entity
        $em->persist($user);
        // update listing ratings
        if (isset($listingReviews[0]['listing_avg']) && $listingReviews[0]['listing_avg'] > 0) {
            // set comments count for listing
            if ($listing->getUser() !== $review->getReviewBy()) {
                $commentCount = $listing->getCommentCount();
                $listing->setCommentCount($commentCount + 1);
            }
            $listing->setAverageRating(ceil($listingReviews[0]['listing_avg']));
            $em->persist($listing);
        }
        $em->flush();
    }

}
<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ReviewBundle\Repository;

use Cocorico\ReviewBundle\Entity\Review;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class ReviewRepository extends EntityRepository
{
    /**
     * getUserAverage calculates and returns the average ratings of users as
     * asker and as offerer.
     *
     * @param  Review $review
     * @return Array
     */
    public function getUserAverage(Review $review)
    {
        $user = $review->getReviewTo();

        $askerQuery = $this->getInitialQuery($user)
            ->addSelect("avg(r.rating) as asker_avg")
            ->andWhere('b.user = :bookingUser')
            ->setParameter('bookingUser', $user)
            ->getQuery();
        $askerRating = $askerQuery->getArrayResult();

        $offererQuery = $this->getInitialQuery($user)
            ->addSelect("avg(r.rating) as offerer_avg")
            ->leftJoin('b.listing', 'l')
            ->andWhere('l.user = :listingUser')
            ->setParameter('listingUser', $user)
            ->getQuery();
        $offererRating = $offererQuery->getArrayResult();

        $avgUserRatings['asker_avg'] = (isset($askerRating[0]['asker_avg'])) ? $askerRating[0]['asker_avg'] : 0;
        $avgUserRatings['offerer_avg'] = (isset($offererRating[0]['offerer_avg'])) ? $offererRating[0]['offerer_avg'] : 0;

        return $avgUserRatings;

    }

    /**
     * getListingAverage calculates and returns the average ratings of listing
     * depending upon the existing inputs for the listing
     *
     * @param  Review $review
     * @return Array
     */
    public function getListingAverage(Review $review)
    {
        $listing = $review->getBooking()->getListing();
        $offerer = $listing->getUser();
        // get rating as asker
        $listingQuery = $this->_em->createQueryBuilder()
            ->from('Cocorico\ReviewBundle\Entity\Review', 'r')
            ->leftJoin('r.booking', 'b')
            ->leftJoin('b.listing', 'l')
            ->addSelect("avg(r.rating) as listing_avg")
            ->where('b.listing = :listing')
            ->andWhere('r.reviewBy != :reviewBy')
            ->setParameter('listing', $listing)
            ->setParameter('reviewBy', $offerer)
            ->groupBy('b.listing')
            ->getQuery();

        $listingRating = $listingQuery->getArrayResult();

        return $listingRating;
    }

    /**
     * getReviewedBookingIds returns ids of bookings which are already reviewed
     * by the provided user
     *
     * @param  User $user
     * @return Array
     */
    public function getReviewedBookingIds(User $user)
    {
        // get rating as asker
        $bookingIds = $this->_em->createQueryBuilder()
            ->from('Cocorico\ReviewBundle\Entity\Review', 'r')
            ->leftJoin('r.booking', 'b')
            ->addSelect("b.id")
            ->where('r.reviewBy = :reviewBy')
            ->setParameter('reviewBy', $user)
            ->getQuery()->getArrayResult();

        return array_map('current', $bookingIds);
    }

    /**
     * getInitialQuery returns the query builder for the review calculations for offerer and askerer
     *
     * @param  User $user
     * @return  QueryBuilder
     */
    private function getInitialQuery(User $user)
    {
        return $this->_em->createQueryBuilder()
            ->from('Cocorico\ReviewBundle\Entity\Review', 'r')
            ->leftJoin('r.booking', 'b')
            ->andWhere('r.reviewTo = :reviewTo')
            ->setParameter('reviewTo', $user)
            ->groupBy('r.reviewTo');
    }

}

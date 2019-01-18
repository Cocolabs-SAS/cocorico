<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ReviewBundle\Model;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Repository\BookingRepository;
use Cocorico\ReviewBundle\Entity\Review;
use Cocorico\ReviewBundle\Repository\ReviewRepository;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;


class ReviewManager extends BaseManager
{
    protected $em;
    protected $maxPerPage;

    /**
     * @param EntityManager $em
     * @param Integer       $maxPerPage
     *
     */
    public function __construct(EntityManager $em, $maxPerPage)
    {
        $this->em = $em;
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * @param  Review $review
     * @return Review
     */
    public function save(Review $review)
    {
        $this->persistAndFlush($review);

        return $review;
    }

    /**
     * getUserReviews will return the paginator object of the reviews added or made for the user object provided
     *
     * @param  string  $userType
     * @param  User    $user
     * @param  string  $type 'made' or 'received'
     * @param  integer $page
     *
     * @return Paginator
     */
    public function getUserReviews($userType, User $user, $type, $page = 1)
    {
        $queryBuilder = $this->getRepository()
            ->createQueryBuilder('r')
            ->addSelect("b, l, t, i, rb, rbi, rt, rti, u")
            ->leftJoin('r.booking', 'b')
            ->leftJoin('b.user', 'u')
            ->leftJoin('b.listing', 'l')
            ->leftJoin('l.images', 'i')
            ->leftJoin('r.reviewBy', 'rb')
            ->leftJoin('rb.images', 'rbi')
            ->leftJoin('r.reviewTo', 'rt')
            ->leftJoin('rt.images', 'rti')
            ->leftJoin('l.translations', 't')
            ->orderBy('r.createdAt', 'DESC');

        // adds the condition about which reviews need to be fetched.
        if ($type == 'made') {
            $queryBuilder->where('r.reviewBy = :user');
        } else {
            $queryBuilder->where('r.reviewTo = :user');
        }

        if ($userType == 'asker') {
            $queryBuilder->andWhere('b.user = :user');
        } else {
            $queryBuilder->andWhere('l.user = :user');
        }

        $queryBuilder->setParameter('user', $user);

        //Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        return new Paginator($query);
    }

    /**
     * userHasReviewed will check if the user already added his/her reviews or not
     *
     * @param  Booking $booking
     * @param  User    $user
     * @return Review | boolean
     */
    public function userHasReviewed(Booking $booking, User $user)
    {
        $queryBuilder = $this->getRepository()
            ->createQueryBuilder('r')
            ->where('r.booking = :booking')
            ->andWhere('r.reviewBy = :reviewBy')
            ->setParameter('booking', $booking)
            ->setParameter('reviewBy', $user);

        try {
            $query = $queryBuilder->getQuery();

            return $query->getOneOrNullResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * getUnreviewedBookings will fetch unreviewed bookings from the booking table
     * by user, and already added reviews
     *
     * @param  string $userType
     * @param  User   $user
     * @return array Booking | Null
     */
    public function getUnreviewedBookings($userType, User $user)
    {
        $reviewedBookingIds = $this->getRepository()->getReviewedBookingIds($user);

        /** @var BookingRepository $bookingRepository */
        $bookingRepository = $this->em->getRepository('CocoricoCoreBundle:Booking');

        $bookings = $bookingRepository->findBookingsToReview($userType, $user->getId(), $reviewedBookingIds);

        return $bookings;
    }

    /**
     * getListingReview returns the list of reviews available for the specific listing
     *
     * @param  Listing $listing
     * @return array objects
     */
    public function getListingReviews(Listing $listing)
    {
        $queryBuilder = $this->getRepository()
            ->createQueryBuilder('r')
            ->addSelect('u')
            ->leftJoin('r.booking', 'b')
            ->leftJoin('b.listing', 'l')
            ->leftJoin('r.reviewBy', 'u')
            ->where('r.reviewBy != :owner')
            ->andWhere('b.listing = :listing')
            ->orderBy('r.createdAt', 'DESC')
            ->setParameter('owner', $listing->getUser())
            ->setParameter('listing', $listing);

        $query = $queryBuilder->getQuery();
        $query->useResultCache(true, 43200, 'getListingReview');

        return $query->getResult();
    }

    /**
     * getRepository description
     *
     * @return ReviewRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('CocoricoReviewBundle:Review');
    }
}
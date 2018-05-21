<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Entity\Booking;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;


class BookingRepository extends EntityRepository
{

    /**
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->addSelect("l, o, t, a, bbw, bpr, auf, ouf, mt")
            ->leftJoin('b.user', 'a')//Asker
            ->leftJoin('a.userFacebook', 'auf')
            ->leftJoin('b.listing', 'l')
            ->leftJoin('b.bankWire', 'bbw')
            ->leftJoin('b.payinRefund', 'bpr')
            ->leftJoin('b.thread', 'mt')
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.user', 'o')//Offerer
            ->leftJoin('o.userFacebook', 'ouf');

        return $queryBuilder;
    }

    /**
     * @param int    $askerId
     * @param string $locale
     * @param array  $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByAskerQuery($askerId, $locale, $status = array())
    {
        $queryBuilder = $this->getFindQueryBuilder();

        $queryBuilder
            ->where('a.id = :askerId')
            ->andWhere('t.locale = :locale')
            ->andWhere('b.status <> :statusDraft')
            ->setParameter('askerId', $askerId)
            ->setParameter('locale', $locale)
            ->setParameter('statusDraft', Booking::STATUS_DRAFT)
            ->orderBy('b.updatedAt', 'desc');

        $status = array_values(array_filter($status));
        if (count($status)) {
            $queryBuilder
                ->andWhere('b.status IN (:status)')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }

    /**
     * @param       $askerId
     * @param       $locale
     * @param array $status
     * @return array
     */
    public function findByAsker($askerId, $locale, $status = array())
    {
        return $this->getFindByAskerQuery($askerId, $locale, $status)->getQuery()->getResult();
    }

    /**
     * @param int    $id
     * @param int    $askerId
     * @param string $locale
     * @param array  $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindOneByAskerQuery($id, $askerId, $locale, $status = array())
    {
        $queryBuilder = $this->getFindByAskerQuery($askerId, $locale, $status);
        $queryBuilder
            ->andWhere('b.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder;
    }


    /**
     * @param int    $offererId
     * @param string $locale
     * @param array  $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByOffererQuery($offererId, $locale, $status = array())
    {
        $queryBuilder = $this->getFindQueryBuilder();

        $queryBuilder
            ->where('o.id = :offererId')
            ->andWhere('t.locale = :locale')
            ->andWhere('b.status <> :statusDraft')
            ->setParameter('offererId', $offererId)
            ->setParameter('locale', $locale)
            ->setParameter('statusDraft', Booking::STATUS_DRAFT)
            ->orderBy('b.updatedAt', 'desc');

        $status = array_values(array_filter($status));
        if (count($status)) {
            $queryBuilder
                ->andWhere('b.status IN (:status)')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }

    /**
     * @param int    $listingId
     * @param string $locale
     * @param array  $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByListingQuery($listingId, $locale, $status = array())
    {
        $queryBuilder = $this->getFindQueryBuilder();

        $queryBuilder
            ->where('l.id = :listingId')
            ->andWhere('t.locale = :locale')
            ->andWhere('b.status <> :statusDraft')
            ->setParameter('listingId', $listingId)
            ->setParameter('locale', $locale)
            ->setParameter('statusDraft', Booking::STATUS_DRAFT)
            ->orderBy('b.updatedAt', 'desc');

        $status = array_values(array_filter($status));
        if (count($status)) {
            $queryBuilder
                ->andWhere('b.status IN (:status)')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }


    /**
     * @param int    $offererId
     * @param string $locale
     * @param array  $status
     *
     * @return array
     */
    public function findByOfferer($offererId, $locale, $status = array())
    {
        return $this->getFindByOffererQuery($offererId, $locale, $status)->getQuery()->getResult();
    }

    /**
     * @param int       $listingId
     * @param string    $locale
     * @param array     $status
     * @param \DateTime $createdAt
     *
     * @return array
     */
    public function findByListingAndLastCreated(
        $listingId,
        $locale,
        $status = array(),
        \DateTime $createdAt
    ) {
        $queryBuilder = $this->getFindByListingQuery($listingId, $locale, $status);

        $queryBuilder
            ->andWhere('b.createdAt >= (:createdAt)')
            ->setParameter('createdAt', $createdAt->format('Y-m-d H:i:s'));

        return $queryBuilder->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     *
     * @param int    $listingId
     * @param string $locale
     * @param array  $status
     *
     * @return array
     */
    public function findByListingAndPayed($listingId, $locale, $status = array())
    {
        $queryBuilder = $this->getFindByListingQuery($listingId, $locale, $status);

        $queryBuilder
            ->andWhere('b.payedBookingAt IS NOT NULL');

        return $queryBuilder->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param int    $listingId
     * @param string $locale
     * @param array  $status
     * @return array
     */
    public function findByListingAndValidated($listingId, $locale, $status = array())
    {
        $queryBuilder = $this->getFindByListingQuery($listingId, $locale, $status);

        $queryBuilder
            ->andWhere('b.validated = :validated')
            ->setParameter('validated', true);

        return $queryBuilder->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Will return the booking objects which are not reviewed yet
     * by the user, even if asker or buyer
     *
     * @param string $userType
     * @param int    $userId
     * @param array  $bookingId
     *
     * @return Booking[]
     */
    public function findBookingsToReview($userType, $userId, $bookingId = array())
    {
        $queryBuilder = $this->getFindQueryBuilder();

        if ($userType == 'asker') {
            $queryBuilder
                ->where('a.id = :askerId')
                ->setParameter('askerId', $userId);

        } else {
            $queryBuilder
                ->where('o.id = :offererId')
                ->setParameter('offererId', $userId);
        }

        if (count($bookingId)) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->notIn('b.id', $bookingId));
        }

        //Booking can be reviewed when booking is validated (booking has begun)
        $queryBuilder
            ->andWhere('b.validated = :validated')
            ->setParameter('validated', true)
            ->orderBy('b.updatedAt', 'desc');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Find expiring bookings to alert
     *
     * @param int    $expirationAlertDelay Delay in minutes to consider a booking as expiring.
     * @param int    $expirationDelay      Delay in minutes to consider a booking as expiring.
     * @param int    $acceptationDelay     Delay in minutes to consider a booking as expiring for acceptation.
     * @param string $timeZone             Default user timezone
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findBookingsExpiringToAlert(
        $expirationAlertDelay,
        $expirationDelay,
        $acceptationDelay,
        $timeZone
    ) {
        $dateExpiring = new \DateTime();
        $dateExpiring->sub(new \DateInterval('PT' . ($expirationDelay - $expirationAlertDelay) . 'M'));

        $dateAcceptationExpiring = new \DateTime('now', new \DateTimeZone($timeZone));
        $dateAcceptationExpiring->add(new \DateInterval('PT' . ($acceptationDelay + $expirationAlertDelay) . 'M'));

        $sql = <<<SQLQUERY
            (
            b.newBookingAt <= :dateExpiring OR
            CONCAT(DATE_FORMAT(b.start, '%Y-%m-%d'), ' ',  DATE_FORMAT(b.startTime, '%H:%i:%s') ) <= :dateAcceptationExpiring
            )
SQLQUERY;

        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('b.status IN (:status)')
            ->andWhere(
                $sql
            )
            ->andWhere('b.alertedExpiring = :alertedExpiring')
            ->setParameter('status', array(Booking::STATUS_NEW))
            ->setParameter('dateExpiring', $dateExpiring->format('Y-m-d H:i:s'))
            ->setParameter('dateAcceptationExpiring', $dateAcceptationExpiring->format('Y-m-d H:i:s'))
            ->setParameter('alertedExpiring', false);

//        echo $queryBuilder->getQuery()->getSQL();
//        print_r($queryBuilder->getQuery()->getParameters()->toArray());

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }


    /**
     * Find imminent Bookings to alert
     *
     * @param int $bookingImminentDelay Delay in minutes to consider a booking as imminent.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findBookingsImminentToAlert($bookingImminentDelay)
    {
        //Imminent date
        $dateImminent = new \DateTime();
        $dateImminent->add(new \DateInterval('PT' . $bookingImminentDelay . 'M'));

        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('b.status IN (:status)')
            ->andWhere('b.start <= :dateImminent')
            ->andWhere('b.alertedImminent = :alertedImminent')
            ->setParameter(
                'status',
                array(
                    Booking::STATUS_PAYED,
                )
            )
            ->setParameter('dateImminent', $dateImminent->format('Y-m-d H:i:s'))
            ->setParameter('alertedImminent', false);

//        echo $queryBuilder->getQuery()->getSQL();
//        print_r($queryBuilder->getQuery()->getParameters()->toArray());

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    /**
     * Find Bookings to expire:
     * Either newBookingAt is less than today minus $bookingExpirationDelay
     * Either booking start date concatenated to start time is less than today date time
     *
     * @param int    $expirationDelay  Delay in minutes to consider a booking as expired.
     * @param int    $acceptationDelay Delay in minutes to consider a booking as expired for acceptation.
     * @param string $timeZone         Default user timezone
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findBookingsToExpire($expirationDelay, $acceptationDelay, $timeZone)
    {
        $today = new \DateTime('now', new \DateTimeZone($timeZone));

        $dateExpired = new \DateTime();
        $dateExpired->sub(new \DateInterval('PT' . $expirationDelay . 'M'));

        $dateAcceptationExpired = new \DateTime('now', new \DateTimeZone($timeZone));
        $dateAcceptationExpired->add(new \DateInterval('PT' . $acceptationDelay . 'M'));

        $sql = <<<SQLQUERY
            (
            b.newBookingAt <= :dateExpired OR
            CONCAT(DATE_FORMAT(b.start, '%Y-%m-%d'), ' ',  DATE_FORMAT(b.startTime, '%H:%i:%s') ) <= :dateAcceptationExpired OR
            CONCAT(DATE_FORMAT(b.start, '%Y-%m-%d'), ' ',  DATE_FORMAT(b.startTime, '%H:%i:%s') ) <= :today
            )
SQLQUERY;

        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('b.status IN (:status)')
            ->andWhere(
                $sql
            )
            ->setParameter(
                'status',
                array(
                    Booking::STATUS_NEW,
                )
            )
            ->setParameter('dateExpired', $dateExpired->format('Y-m-d H:i:s'))
            ->setParameter('dateAcceptationExpired', $dateAcceptationExpired->format('Y-m-d H:i:s'))
            ->setParameter('today', $today->format('Y-m-d H:i:s'));

//        echo $queryBuilder->getQuery()->getSQL();
//        print_r($queryBuilder->getQuery()->getParameters()->toArray());

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }


    /**
     * Find bookings to refuse because an other one has been accepted.
     * When a booking is accepted by the offerer other bookings request for the same listing
     * in the date range of the accepted booking are refused
     *
     * @link: http://sqlfiddle.com/#!9/7ec20/1
     *
     * @param Booking $bookingAccepted
     * @param bool    $endDayIncluded
     * @param bool    $timeUnitIsDay
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findBookingsToRefuse($bookingAccepted, $endDayIncluded, $timeUnitIsDay)
    {
        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('b.status IN (:status)')
            ->andWhere('b.end >= :start')
            ->andWhere('b.listing = :listing');

        //If end day is included in booking, we refuse booking starting at this end date
        if ($endDayIncluded) {
            $queryBuilder
                ->andWhere('b.start <= :end');
        } else {
            $queryBuilder
                ->andWhere('b.start < :end');
        }

        $queryBuilder
            ->setParameter(
                'status',
                array(
                    Booking::STATUS_NEW,
                )
            )
            ->setParameter('start', $bookingAccepted->getStart()->format('Y-m-d H:i:s'))
            ->setParameter('end', $bookingAccepted->getEnd()->format('Y-m-d H:i:s'))
            ->setParameter('listing', $bookingAccepted->getListing());

        //If time unit is not day we refuse bookings with time range overlapping the accepted booking time range 
        if (!$timeUnitIsDay) {
            $queryBuilder
                ->andWhere('b.startTime < :endTime')
                ->andWhere('b.endTime > :startTime')
                ->setParameter('startTime', $bookingAccepted->getStartTime()->format('Y-m-d H:i:s'))
                ->setParameter('endTime', $bookingAccepted->getEndTime()->format('Y-m-d H:i:s'));
        }


//        echo $queryBuilder->getQuery()->getSQL();
//        print_r($queryBuilder->getQuery()->getParameters()->toArray());

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }


    /**
     * Find Bookings to validate
     *
     * @param string $validatedMoment 'start' or 'end'
     *                                Does the booking object (apartment, service, ...) is considered as validated (Offerer can be payed)
     *                                after booking start date or booking end date.
     * @param int    $validatedDelay  Time after or before the moment the booking is considered as validated (in minutes)
     * @param string $timeZone        Default user time zone
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|Booking[]
     *
     * @throws \Exception
     */
    public function findBookingsToValidate($validatedMoment, $validatedDelay, $timeZone)
    {
        if ($validatedMoment != 'start' && $validatedMoment != 'end') {
            throw new \Exception('Wrong argument $validatedMoment in findBookingsToValidate function');
        }

        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('b.status IN (:status)')
            ->andWhere('b.validated = :validated')
            ->setParameter(
                'status',
                array(
                    Booking::STATUS_PAYED,
                )
            )
            ->setParameter('validated', false);

        $dateValidation = new \DateTime('now', new \DateTimeZone($timeZone));
        if ($validatedDelay >= 0) {//after moment
            $dateValidation->sub(new \DateInterval('PT' . $validatedDelay . 'M'));
        } else {//before moment
            $dateValidation->add(new \DateInterval('PT' . abs($validatedDelay) . 'M'));
        }

        $sql = <<<SQLQUERY
            CONCAT(DATE_FORMAT(b.{$validatedMoment}, '%Y-%m-%d'), ' ',  DATE_FORMAT(b.{$validatedMoment}Time, '%H:%i:%s') ) <= :dateValidation
SQLQUERY;

        $queryBuilder
            ->andWhere($sql)
            ->setParameter('dateValidation', $dateValidation->format('Y-m-d H:i:s'));

//        echo $queryBuilder->getQuery()->getSQL();
//        print_r($queryBuilder->getQuery()->getParameters()->toArray());
//die();
        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

}

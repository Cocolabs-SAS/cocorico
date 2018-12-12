<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Repository;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Repository\BookingRepository as BaseBookingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;

class BookingRepository extends BaseBookingRepository
{

    public function __construct($em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    /**
     * Find Bookings deposit to refund. Booking deposit to refund are generated once the booking has finished and has
     * been validated.
     *
     * @param int    $depositRefundDelay Time after the end of the booking from which the deposit refund can be
     *                                   generated (in minutes)
     *
     * @param string $timeZone           Default user time zone
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|Booking[]
     *
     * @throws \Exception
     */
    public function findBookingsDepositToRefund($depositRefundDelay, $timeZone)
    {
        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->leftJoin('b.depositRefund', 'bdr')
            ->where('b.status IN (:status)')
            ->andWhere('b.validated = :validated')
            ->andWhere('(b.amountDeposit IS NOT NULL AND b.amountDeposit > 0)')
            ->groupBy('b.id')
//            ->having('COUNT(bdr.id) = 0')
            ->setParameter(
                'status',
                array(
                    Booking::STATUS_PAYED,
                )
            )
            ->setParameter('validated', true);

        $dateRefunding = new \DateTime('now', new \DateTimeZone($timeZone));
        if ($depositRefundDelay >= 0) {//after end
            $dateRefunding->sub(new \DateInterval('PT' . $depositRefundDelay . 'M'));
        } else {//before end
            $dateRefunding->add(new \DateInterval('PT' . abs($depositRefundDelay) . 'M'));
//            throw new \Exception(
//                'Wrong argument $depositRefundDelay in findBookingsDepositToRefund method: Must be >= 0.'
//            );
        }

        $sql = <<<SQLQUERY
            CONCAT(DATE_FORMAT(b.end, '%Y-%m-%d'), ' ',  DATE_FORMAT(b.endTime, '%H:%i:%s') ) <= :dateRefunding
SQLQUERY;

        $queryBuilder
            ->andWhere($sql)
            ->setParameter('dateRefunding', $dateRefunding->format('Y-m-d H:i:s'));

//        echo $queryBuilder->getQuery()->getSQL();
//        print_r($queryBuilder->getQuery()->getParameters()->toArray());
//        die();

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

}

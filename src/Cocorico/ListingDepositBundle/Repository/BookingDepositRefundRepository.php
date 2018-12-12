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

use Cocorico\ListingDepositBundle\Entity\BookingDepositRefund;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;


class BookingDepositRefundRepository extends EntityRepository
{
    /**
     * Find Bookings deposit refund to check
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     *
     * @throws \Exception
     */
    public function findBookingsDepositRefundsToCheck()
    {
        $queryBuilder = $this->createQueryBuilder('bdr');
        $queryBuilder
            ->where(('bdr.statusAsker = :status OR bdr.statusOfferer = :status'))
            ->setParameter(
                'status',
                array(
                    BookingDepositRefund::STATUS_TO_DO,
                )
            );

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

}

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

use Cocorico\CoreBundle\Entity\BookingBankWire;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class BookingBankWireRepository extends EntityRepository
{

    /**
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('bbw')
            ->addSelect("bbw, b, o")
            ->leftJoin('bbw.booking', 'b')
            ->leftJoin('bbw.user', 'o')//Offerer
            ->orderBy('bbw.createdAt', 'desc');

        return $queryBuilder;
    }

    /**
     * @param int   $offererId
     * @param array $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByOffererQuery($offererId, $status = array())
    {
        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('o.id = :offererId')
            ->setParameter('offererId', $offererId);

        $status = array_values(array_filter($status));
        if (count($status)) {
            $queryBuilder
                ->andWhere('bbw.status IN (:status)')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }

    /**
     * @param int   $id
     * @param int   $offererId
     * @param array $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindOneByOffererQuery($id, $offererId, $status = array())
    {
        $queryBuilder = $this->getFindByOffererQuery($offererId, $status);
        $queryBuilder
            ->andWhere('bbw.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder;
    }


    /**
     * Find Bookings Bank Wires to check
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     *
     * @throws \Exception
     */
    public function findBookingsBankWiresToCheck()
    {
        $queryBuilder = $this->createQueryBuilder('bbw');
        $queryBuilder
            ->where('bbw.status = :status')
            ->andWhere('bbw.payedAt IS NULL')
            ->setParameter(
                'status',
                array(
                    BookingBankWire::STATUS_DONE,
                )
            );

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

}

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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;


class BookingPayinRefundRepository extends EntityRepository
{

    /**
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('bpr')
            ->addSelect("bpr, b, a")
            ->leftJoin('bpr.booking', 'b')
            ->leftJoin('bpr.user', 'a')//Asker
            ->orderBy('bpr.createdAt', 'desc');

        return $queryBuilder;
    }

    /**
     * @param int   $askerId
     * @param array $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByAskerQuery($askerId, $status = array())
    {
        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('a.id = :askerId')
            ->setParameter('askerId', $askerId);

        $status = array_values(array_filter($status));
        if (count($status)) {
            $queryBuilder
                ->andWhere('bpr.status IN (:status)')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }

    /**
     * @param int   $id
     * @param int   $askerId
     * @param array $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindOneByAskerQuery($id, $askerId, $status = array())
    {
        $queryBuilder = $this->getFindQueryBuilder();
        $queryBuilder
            ->where('bpr.id = :id')
            ->andWhere('a.id = :askerId')
            ->setParameter('id', $id)
            ->setParameter('askerId', $askerId);

        $status = array_values(array_filter($status));
        if (count($status)) {
            $queryBuilder
                ->andWhere('bpr.status IN (:status)')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }


}

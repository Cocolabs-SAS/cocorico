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

use Cocorico\CoreBundle\Entity\ListingDiscount;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class ListingDiscountRepository extends EntityRepository
{
    /**
     * @param int $listingId
     * @param int $fromQuantity
     * @return ListingDiscount|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByFromQuantity($listingId, $fromQuantity)
    {
        $queryBuilder = $this->createQueryBuilder('ld')
            ->leftJoin('ld.listing', 'l')
            ->where('l.id = :listingId')
            ->andWhere('ld.fromQuantity <= :fromQuantity')
            ->setParameter('listingId', $listingId)
            ->setParameter('fromQuantity', $fromQuantity)
            ->orderBy('ld.fromQuantity', 'DESC')
            ->setMaxResults(1);
        try {
            $query = $queryBuilder->getQuery();

            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }


}

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

use Cocorico\CoreBundle\Entity\ListingCharacteristicType;
use Doctrine\ORM\EntityRepository;

class ListingCharacteristicValueRepository extends EntityRepository
{
    /**
     * @param ListingCharacteristicType $listingCharacteristicType
     * @param                           $locale
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllTranslatedQueryBuilder(ListingCharacteristicType $listingCharacteristicType, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('lcv')
            ->leftJoin('lcv.translations', 'lcvt')
            ->andWhere('lcv.listingCharacteristicType = :lcr')
            ->andWhere('lcvt.locale = :locale')
            ->setParameter('lcr', $listingCharacteristicType)
            ->setParameter('locale', $locale);


//        $queryBuilder->getQuery()->useQueryCache(true);
//        $queryBuilder->getQuery()->useResultCache(true, 3600, 'listing_characteristic_values');
        return $queryBuilder;
    }

    /**
     * @param string $locale
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
//    public function findAllTranslated($locale)
//    {
//        return $this->getFindAllTranslatedQueryBuilder($locale)
//            ->getQuery()
//            ->getResult();
//
//    }
}

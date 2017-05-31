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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class ListingCharacteristicRepository extends EntityRepository
{
    /**
     * @param $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllTranslatedQueryBuilder($locale)
    {
        $queryBuilder = $this->createQueryBuilder('lc')
            ->addSelect("lct, lcg, lcgt, lcty, lcv, lcvt")
            ->leftJoin('lc.translations', 'lct')
            ->leftJoin('lc.listingCharacteristicGroup', 'lcg')
            ->leftJoin('lcg.translations', 'lcgt')
            ->leftJoin('lc.listingCharacteristicType', 'lcty')
            ->leftJoin('lcty.listingCharacteristicValues', 'lcv')
            ->leftJoin('lcv.translations', 'lcvt')
            ->where('lct.locale = :locale')
            ->andWhere('lcgt.locale = :locale')
            ->andWhere('lcvt.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('lcg.position', 'ASC')
            ->addOrderBy('lc.position', 'ASC');

//        $queryBuilder->getQuery()->useQueryCache(true);
//        $queryBuilder->getQuery()->useResultCache(true, 3600, 'listing_characteristics');

        return $queryBuilder;
    }

    /**
     * @param string $locale
     *
     * @return ArrayCollection|array|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllTranslated($locale)
    {
        return $this->getFindAllTranslatedQueryBuilder($locale)
            ->getQuery()
            ->getResult();
    }


    /**
     * @param $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
//    public function getFindAllTranslatedWithValuesQueryBuilder($locale)
//    {
//        $queryBuilder = $this->createQueryBuilder('lc')
//            ->addSelect("lct, lctt, lcv, lcvt")
//            ->leftJoin('lc.translations', 'lct')
//            ->leftJoin('lc.listingCharacteristicType', 'lctt')
//            ->leftJoin('lctt.listingCharacteristicValues', 'lcv')
//            ->leftJoin('lcv.translations', 'lcvt')
//            ->andWhere('lct.locale = :locale')
//            ->setParameter('locale', $locale);
//
//        return $queryBuilder;
//    }

    /**
     * @param string $locale
     *
     * @return ArrayCollection|array|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
//    public function findAllTranslatedValues($locale)
//    {
//        return $this->getFindAllTranslatedWithValuesQueryBuilder($locale)
//            ->getQuery()
//            ->getResult();
//
//    }
}

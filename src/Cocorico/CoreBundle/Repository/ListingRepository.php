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

use Cocorico\CoreBundle\Entity\Listing;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;

class ListingRepository extends EntityRepository
{
    /**
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindQueryBuilder()
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            //Select
            ->select('partial l.{id, price, averageRating, certified, createdAt}')
            ->addSelect("partial t.{id, locale, slug, title, description}")
            ->addSelect("partial ca.{id, lft, lvl, rgt, root}")
            ->addSelect("partial cat.{id, locale, name}")
            ->addSelect("partial i.{id, name}")
            ->addSelect("partial u.{id, firstName}")
            //->addSelect("partial ln.{id}")
            ->addSelect("partial ln.{id, city, route, country}")
            ->addSelect("partial co.{id, lat, lng}")
            ->addSelect("partial ui.{id, name}")
            ->addSelect("'' AS DUMMY")//To maintain fields on same array level when extra fields are added

            //From
            ->from('CocoricoCoreBundle:Listing', 'l')
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.categories', 'ca')
            //Join::WITH: Avoid exclusion of listings with no categories (disable inner join)
            ->leftJoin('ca.translations', 'cat', Query\Expr\Join::WITH, 'cat.locale = :locale')
            ->leftJoin('l.images', 'i')
            ->leftJoin('l.user', 'u')
            ->leftJoin('u.images', 'ui', Query\Expr\Join::WITH, 'ui.position = 1')
            ->leftJoin('l.location', 'ln')
            ->leftJoin('ln.coordinate', 'co');

//            ->leftJoin('co.country', 'cy');

        $queryBuilder
            ->addGroupBy('l.id');

        return $queryBuilder;
    }

    /**
     * @param string $slug
     * @param string $locale
     * @param bool   $joined
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlug($slug, $locale, $joined = true)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("t")
            ->leftJoin('l.translations', 't')
            ->where('t.slug = :slug')
            ->andWhere('t.locale = :locale')
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale);

        if ($joined) {
            $queryBuilder
                ->addSelect("u, i")
                ->leftJoin('l.user', 'u')
                ->leftJoin('u.images', 'i');
        }

//          $queryBuilder
//            ->addSelect("llc, lc, lct")
//            ->leftJoin('l.listingListingCharacteristics', 'llc')
//            ->leftJoin('llc.listingCharacteristic', 'lc')
//            ->leftJoin('lc.translations', 'lct')
//            ->leftJoin('lc.listingCharacteristicGroup', 'lcg')
//            ->leftJoin('lc.translations', 'lcgt')
//            ->andWhere('lct.locale = :locale')
//            ->andWhere('lcgt.locale = :locale');

        try {
            $query = $queryBuilder->getQuery();
//            $query->useResultCache(true, 3600, 'findOneBySlug');
//            $query->setFetchMode("Cocorico\CoreBundle\Entity\Listing", "listingListingCharacteristics", ClassMetadata::FETCH_EAGER);
//            $query->setFetchMode("Cocorico\CoreBundle\Entity\ListingListingCharacteristic", "listingCharacteristic", ClassMetadata::FETCH_EAGER);
//            $query->setFetchMode("Cocorico\CoreBundle\Entity\ListingCharacteristic", "ListingCharacteristicGroup", ClassMetadata::FETCH_EAGER);

            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param string $slug
     * @param string $locale
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTranslationsBySlug($slug, $locale)
    {
        $listing = $this->findOneBySlug($slug, $locale, false);

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('lt')
            ->from('CocoricoCoreBundle:ListingTranslation', 'lt')
            ->where('lt.translatable = :listing')
            ->setParameter('listing', $listing);
        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param int    $ownerId
     * @param string $locale
     * @param array  $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByOwnerQuery($ownerId, $locale, $status)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("t, i, c, ca, cat, u")
//            ->addSelect("t, i, c, ca, cat, u, rt")
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.user', 'u')
            //->leftJoin('u.reviewsTo', 'rt')
            ->leftJoin('l.listingListingCharacteristics', 'c')
            ->leftJoin('l.images', 'i')
            ->leftJoin('l.categories', 'ca')
            ->leftJoin('ca.translations', 'cat')
            ->where('u.id = :ownerId')
            ->andWhere('t.locale = :locale')
            ->andWhere('l.status IN (:status)')
            //->andWhere('rt.reviewTo = :reviewTo')
            ->setParameter('ownerId', $ownerId)
            ->setParameter('locale', $locale)
            ->setParameter('status', $status);

        //->setParameter('reviewTo', $ownerId);

        return $queryBuilder;

    }

    /**
     * @param $ownerId
     * @param $locale
     * @param $status
     * @return array
     */
    public function findByOwner($ownerId, $locale, $status)
    {
        return $this->getFindByOwnerQuery($ownerId, $locale, $status)->getQuery()->getResult();
    }


    /**
     * @param $title
     * @param $locale
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByTitle($title, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("t")
            ->addSelect("u, i")
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.user', 'u')
            ->leftJoin('u.images', 'i')
            ->where('t.title = :title')
            ->andWhere('t.locale = :locale')
            ->setParameter('title', $title)
            ->setParameter('locale', $locale);
        try {

            $query = $queryBuilder->getQuery();

            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param bool $withUser
     * @param bool $withTranslations
     *
     * @param int  $hydrationMode
     * @return array|null
     */
    public function findAllPublished(
        $withUser = true,
        $withTranslations = false,
        $hydrationMode = AbstractQuery::HYDRATE_OBJECT
    ) {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.status = :listingStatus')
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED);

        if ($withUser) {
            $queryBuilder
                ->addSelect("u")
                ->leftJoin('l.user', 'u');
        }

        if ($withTranslations) {
            $queryBuilder
                ->addSelect("t")
                ->leftJoin('l.translations', 't');
        }

        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult($hydrationMode);
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function findByHighestRanking($limit, $locale)
    {
        $queryBuilder = $this->getFindQueryBuilder();

        //Where
        $queryBuilder
            ->where('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED)
            ->setMaxResults($limit)
            ->orderBy('l.createdAt', 'DESC');
        try {
            $query = $queryBuilder->getQuery();
            $query->useResultCache(true, 21600, 'findByHighestRanking');

            return $query->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}

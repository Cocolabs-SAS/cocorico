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
            ->select('partial l.{id, price, averageRating, certified}')
            ->addSelect("partial t.{id, locale, slug, title, description}")
            ->addSelect("partial ca.{id}")
            ->addSelect("partial cat.{id, locale, name}")
            ->addSelect("partial i.{id, name}")
            ->addSelect("partial u.{id, firstName}")
            //->addSelect("partial ln.{id}")
            ->addSelect("partial ln.{id, city, route}")//for test
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
            ->leftJoin('u.images', 'ui')
            ->leftJoin('l.location', 'ln')
            ->leftJoin('ln.coordinate', 'co');
//            ->leftJoin('co.country', 'cy');

        return $queryBuilder;
    }

    /**
     * @param $slug
     * @param $locale
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlug($slug, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("t")
            ->addSelect("u, i")
//            ->addSelect("llc, lc, lct")
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.user', 'u')
            ->leftJoin('u.images', 'i')
//            ->leftJoin('l.listingListingCharacteristics', 'llc')
//            ->leftJoin('llc.listingCharacteristic', 'lc')
//            ->leftJoin('lc.translations', 'lct')
//            ->leftJoin('lc.listingCharacteristicGroup', 'lcg')
//            ->leftJoin('lc.translations', 'lcgt')
            ->where('t.slug = :slug')
            ->andWhere('t.locale = :locale')
//            ->andWhere('lct.locale = :locale')
//            ->andWhere('lcgt.locale = :locale')
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale);
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
     * @param $slug
     * @param $locale
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTranslationsBySlug($slug, $locale)
    {
        $listing = $this->findOneBySlug($slug, $locale);

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
     * @return array|null
     */
    public function findPublishedListing()
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect("u")
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.user', 'u')
            ->where('l.status = :listingStatus')
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED);
        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function findByHighestRanking($limit, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->addSelect('t, i, c, ct, u, ut, uf, ll')
            ->leftJoin('l.translations', 't')
            ->leftJoin('l.images', 'i')
            ->leftJoin('l.location', 'll')
            ->leftJoin('l.user', 'u')
            ->leftJoin('u.translations', 'ut')
            ->leftJoin('u.userFacebook', 'uf')
            ->leftJoin('l.categories', 'c')
            ->leftJoin('c.translations', 'ct')
            ->where('l.status = :listingStatus')
            ->andWhere('t.locale = :locale')
            ->andWhere('ct.locale = :locale')
//            ->andWhere('ut.locale = :locale')
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED)
            ->setParameter('locale', $locale)
            ->setMaxResults($limit)
            ->orderBy('l.createdAt', 'DESC')
            ->groupBy('l.id');
        try {
            $query = $queryBuilder->getQuery();
            $query->useResultCache(true, 21600, 'findByHighestRanking');

            return $query->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Repository;

use Cocorico\PageBundle\Entity\PageTranslation;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class PageRepository extends EntityRepository
{
    /**
     * @param $slug
     * @param $locale
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlug($slug, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->addSelect("t")
            ->leftJoin('p.translations', 't')
            ->where('t.slug = :slug')
            ->andWhere('t.locale = :locale')
            ->andWhere('p.published = :published')
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale)
            ->setParameter('published', true);
        try {
            $query = $queryBuilder->getQuery();

            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param $slug
     * @param $locale
     * @return array|null
     */
    public function findTranslationsBySlug($slug, $locale)
    {
        $page = $this->findOneBySlug($slug, $locale);

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('pt')
            ->from('CocoricoPageBundle:PageTranslation', 'pt')
            ->where('pt.translatable = :page')
            ->setParameter('page', $page);
        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @return array|null
     */
    public function findAllPublished()
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->addSelect("t")
            ->leftJoin('p.translations', 't')
            ->andWhere('p.published = :published')
            ->setParameter('published', true)
            ->orderBy('p.id', 'ASC')
            ->addOrderBy('t.locale', 'ASC');
        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return null;
        }
    }

}

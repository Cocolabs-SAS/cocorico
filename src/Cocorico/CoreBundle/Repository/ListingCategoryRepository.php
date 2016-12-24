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

use Cocorico\CoreBundle\Entity\ListingCategory;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ListingCategoryRepository extends NestedTreeRepository
{
    protected $rootAlias;

    /**
     * @param $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNodesHierarchyTranslatedQueryBuilder($locale)
    {
        $qb = $this->getNodesHierarchyQueryBuilder();

        $alias = $qb->getRootAliases();
        $this->rootAlias = $alias[0];

        $qb
            ->addSelect('t')
            ->leftJoin($this->rootAlias . ".translations", 't')
            ->andWhere('t.locale = :locale')
            ->setParameter('locale', $locale);

        return $qb;
    }

    /**
     * @param  string $locale
     * @return ListingCategory[]|mixed
     */
    public function findCategories($locale)
    {
        $qb = $this->getNodesHierarchyTranslatedQueryBuilder($locale);

        $query = $qb->getQuery();
        $query->useResultCache(true, 43200, 'findCategories');

        return $query->execute();
    }

    /**
     * @param array   $ids
     * @param  string $locale
     * @return ListingCategory[]|mixed
     */
    public function findCategoriesByIds(array $ids, $locale)
    {
        $qb = $this->getNodesHierarchyTranslatedQueryBuilder($locale);
        $qb->andWhere($this->rootAlias . ".id IN (:ids)")
            ->setParameter('ids', $ids);


        $query = $qb->getQuery();
        $query->useResultCache(true, 43200, 'findCategoriesByIds');

        return $query->execute();
    }

}

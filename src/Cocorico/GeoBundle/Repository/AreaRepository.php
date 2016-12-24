<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\Repository;

use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\Country;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class AreaRepository extends EntityRepository
{
    /**
     * @param string  $name
     * @param Country $country
     * @return Area|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByNameAndCountry($name, $country)
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->addSelect("at, ag")
            ->leftJoin('a.translations', 'at')
            ->leftJoin('a.geocoding', 'ag')
            ->where('at.name = :name')
            ->andWhere('a.country = :country')
            ->setParameter('name', $name)
            ->setParameter('country', $country);
        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @return array|null
     */
    public function findAllAreas()
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->addSelect("c, ag, at")
            ->leftJoin('a.country', 'c')
            ->leftJoin('a.translations', 'at')
            ->leftJoin('a.geocoding', 'ag')
            ->orderBy('at.name');
        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return null;
        }
    }

}

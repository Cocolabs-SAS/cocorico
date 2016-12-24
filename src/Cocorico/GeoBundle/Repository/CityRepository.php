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

use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Department;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * CityRepository
 *
 */
class CityRepository extends EntityRepository
{
    /**
     * @param string     $name
     * @param Department $department
     * @return City|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByNameAndDepartment($name, $department)
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->addSelect("ct, cg")
            ->leftJoin('c.translations', 'ct')
            ->leftJoin('c.geocoding', 'cg')
            ->where('ct.name = :name')
            ->andWhere('c.department = :department')
            ->setParameter('name', $name)
            ->setParameter('department', $department);

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }


    /**
     * @return array|null
     */
    public function findAllCities()
    {
        $queryBuilder = $this->createQueryBuilder('ci')
            ->addSelect("c, cig, cit")
            ->leftJoin('ci.country', 'c')
            ->leftJoin('ci.translations', 'cit')
            ->leftJoin('ci.geocoding', 'cig')
            ->orderBy('cit.name');
        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return null;
        }
    }
}

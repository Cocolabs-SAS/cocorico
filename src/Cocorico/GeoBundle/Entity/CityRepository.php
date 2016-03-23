<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * CityRepository
 *
 */
class CityRepository extends EntityRepository
{
    /**
     * @param $name
     * @param $department
     * @return City|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByNameAndDepartment($name, $department)
    {

        $queryBuilder = $this->createQueryBuilder('c')
            ->addSelect("c")
            ->leftJoin('c.translations', 't')
            ->where('t.name = :name')
            ->andWhere('c.department = :department')
            ->setParameter('name', $name)
            ->setParameter('department', $department);

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }

    }
}

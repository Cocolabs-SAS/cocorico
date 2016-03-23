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

class AreaRepository extends EntityRepository
{
    /**
     * @param $name
     * @param $country
     * @return mixed|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByNameAndCountry($name, $country)
    {

        $queryBuilder = $this->createQueryBuilder('a')
            ->addSelect("a")
            ->leftJoin('a.translations', 't')
            ->where('t.name = :name')
            ->andWhere('a.country = :country')
            ->setParameter('name', $name)
            ->setParameter('country', $country);

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }

    }
}

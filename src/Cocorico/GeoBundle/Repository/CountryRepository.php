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

use Cocorico\GeoBundle\Entity\Country;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class CountryRepository extends EntityRepository
{

    /**
     * @param $code
     * @return Country|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByCode($code)
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->addSelect("ct, cg")
            ->leftJoin('c.translations', 'ct')
            ->leftJoin('c.geocoding', 'cg')
            ->where('c.code = :code')
            ->setParameter('code', $code);
        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @return array|null
     */
    public function findAllCountries()
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->addSelect("cg, ct")
            ->leftJoin('c.translations', 'ct')
            ->leftJoin('c.geocoding', 'cg')
            ->orderBy('ct.name');
        try {
            $query = $queryBuilder->getQuery();

            return $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return null;
        }
    }
}

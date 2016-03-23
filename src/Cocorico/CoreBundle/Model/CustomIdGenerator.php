<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Model;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class CustomIdGenerator extends AbstractIdGenerator
{
    /**
     * Generate random id entity
     *
     * @param EntityManager                $em
     * @param \Doctrine\ORM\Mapping\Entity $entity
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public function generate(EntityManager $em, $entity)
    {
        $entityName = $em->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();

        //Seed
        list($uSec, $sec) = explode(' ', microtime());
        $seed = (float)$sec + ((float)$uSec * 100000);
        mt_srand($seed);

        while (true) {
            $id = mt_rand(10000, 2147483640);
            $item = $em->find($entityName, $id);

            if (!$item) {
                return $id;
            }
        }

        throw new \Exception('Id Generator error');
    }
}
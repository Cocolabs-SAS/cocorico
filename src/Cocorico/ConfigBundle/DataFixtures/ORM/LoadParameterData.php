<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ConfigBundle\DataFixtures\ORM;

use Cocorico\ConfigBundle\Entity\Parameter;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadParameterData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var  ContainerInterface container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $parameters = $this->container->getParameter('cocorico_config.parameters_allowed');
        if (is_array($parameters) && count($parameters)) {
            foreach ($parameters as $parameterName => $parameterInfo) {
                $parameter = new Parameter();
                $parameter->setName($parameterName);
                $parameter->setType($parameterInfo['type']);
                $parameter->setValue(null);
                $manager->persist($parameter);
            }

            $manager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }

}

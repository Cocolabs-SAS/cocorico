<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\CurrencyBundle\Entity\Currency;

class LoadCurrencyData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $currency = new Currency();
        $currency->setCode('EUR');
        $currency->setRate(1.0000);
        $manager->persist($currency);

        $currency = new Currency();
        $currency->setCode('USD');
        $currency->setRate(1.2448);
        $manager->persist($currency);

        $currency = new Currency();
        $currency->setCode('JPY');
        $currency->setRate(145.8900);
        $manager->persist($currency);

        $currency = new Currency();
        $currency->setCode('GBP');
        $currency->setRate(0.7932);
        $manager->persist($currency);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }

}

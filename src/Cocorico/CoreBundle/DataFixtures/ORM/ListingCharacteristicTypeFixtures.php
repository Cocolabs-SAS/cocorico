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

use Cocorico\CoreBundle\Entity\ListingCharacteristicType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ListingCharacteristicTypeFixtures extends Fixture
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $listingCharacteristicType = new ListingCharacteristicType();
        $listingCharacteristicType->setName("Yes/No");
        $manager->persist($listingCharacteristicType);
        $manager->flush();
        $this->addReference('characteristic_type_yes_no', $listingCharacteristicType);

        $listingCharacteristicType = new ListingCharacteristicType();
        $listingCharacteristicType->setName("Quantity");
        $manager->persist($listingCharacteristicType);
        $manager->flush();
        $this->addReference('characteristic_type_quantity', $listingCharacteristicType);

        $listingCharacteristicType = new ListingCharacteristicType();
        $listingCharacteristicType->setName("Custom_1");
        $manager->persist($listingCharacteristicType);
        $manager->flush();
        $this->addReference('characteristic_type_custom_1', $listingCharacteristicType);
    }

}

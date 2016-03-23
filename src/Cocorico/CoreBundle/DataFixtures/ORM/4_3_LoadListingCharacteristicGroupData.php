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

use Cocorico\CoreBundle\Entity\ListingCharacteristicGroup;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadListingCharacteristicGroupData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $listingCharacteristicGroup = new ListingCharacteristicGroup();
        $listingCharacteristicGroup->setPosition(1);
        $listingCharacteristicGroup->translate('en')->setName('Group_1');
        $listingCharacteristicGroup->translate('fr')->setName('Groupe_1');
        $manager->persist($listingCharacteristicGroup);
        $listingCharacteristicGroup->mergeNewTranslations();
        $manager->flush();
        $this->addReference('group_1', $listingCharacteristicGroup);

        $listingCharacteristicGroup = new ListingCharacteristicGroup();
        $listingCharacteristicGroup->setPosition(2);
        $listingCharacteristicGroup->translate('en')->setName('Group_2');
        $listingCharacteristicGroup->translate('fr')->setName('Groupe_2');
        $manager->persist($listingCharacteristicGroup);
        $listingCharacteristicGroup->mergeNewTranslations();
        $manager->flush();
        $this->addReference('group_2', $listingCharacteristicGroup);

    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6;
    }

}

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

use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingCharacteristicGroup;
use Cocorico\CoreBundle\Entity\ListingCharacteristicType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadListingCharacteristicData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $listingCharacteristic = new ListingCharacteristic();
        $listingCharacteristic->setPosition(1);
        $listingCharacteristic->translate('en')->setName('Characteristic_1');
        $listingCharacteristic->translate('fr')->setName('Caractéristique_1');
        $listingCharacteristic->translate('en')->setDescription('Characteristic_1 description');
        $listingCharacteristic->translate('fr')->setDescription('Description de la Caractéristique_1');
        /** @var ListingCharacteristicType $listingCharacteristicType */
        $listingCharacteristicType = $manager->merge($this->getReference('characteristic_type_yes_no'));
        $listingCharacteristic->setListingCharacteristicType($listingCharacteristicType);
        /** @var ListingCharacteristicGroup $listingCharacteristicGroup */
        $listingCharacteristicGroup = $manager->merge($this->getReference('group_1'));
        $listingCharacteristic->setListingCharacteristicGroup($listingCharacteristicGroup);

        $manager->persist($listingCharacteristic);
        $listingCharacteristic->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_1', $listingCharacteristic);


        $listingCharacteristic = new ListingCharacteristic();
        $listingCharacteristic->setPosition(2);
        $listingCharacteristic->translate('en')->setName('Characteristic_2');
        $listingCharacteristic->translate('fr')->setName('Caractéristique_2');
        $listingCharacteristic->translate('en')->setDescription('Characteristic_2 description');
        $listingCharacteristic->translate('fr')->setDescription('Description de la Caractéristique_2');
        $listingCharacteristicType = $manager->merge($this->getReference('characteristic_type_quantity'));
        $listingCharacteristic->setListingCharacteristicGroup($listingCharacteristicGroup);
        $listingCharacteristic->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristic);
        $listingCharacteristic->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_2', $listingCharacteristic);

        $listingCharacteristic = new ListingCharacteristic();
        $listingCharacteristic->setPosition(3);
        $listingCharacteristic->translate('en')->setName('Characteristic_3');
        $listingCharacteristic->translate('fr')->setName('Caractéristique_3');
        $listingCharacteristic->translate('en')->setDescription('Characteristic_3 description');
        $listingCharacteristic->translate('fr')->setDescription('Description de la Caractéristique_3');
        $listingCharacteristicType = $manager->merge($this->getReference('characteristic_type_custom_1'));
        $listingCharacteristic->setListingCharacteristicType($listingCharacteristicType);
        $listingCharacteristicGroup = $manager->merge($this->getReference('group_2'));
        $listingCharacteristic->setListingCharacteristicGroup($listingCharacteristicGroup);
        $manager->persist($listingCharacteristic);
        $listingCharacteristic->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_3', $listingCharacteristic);


        $listingCharacteristic = new ListingCharacteristic();
        $listingCharacteristic->setPosition(4);
        $listingCharacteristic->translate('en')->setName('Characteristic_4');
        $listingCharacteristic->translate('fr')->setName('Caractéristique_4');
        $listingCharacteristic->translate('en')->setDescription('Characteristic_4 description');
        $listingCharacteristic->translate('fr')->setDescription('Description de la Caractéristique_4');
        $listingCharacteristicType = $manager->merge($this->getReference('characteristic_type_custom_1'));
        $listingCharacteristic->setListingCharacteristicType($listingCharacteristicType);
        $listingCharacteristicGroup = $manager->merge($this->getReference('group_2'));
        $listingCharacteristic->setListingCharacteristicGroup($listingCharacteristicGroup);
        $manager->persist($listingCharacteristic);
        $listingCharacteristic->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_4', $listingCharacteristic);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7;
    }

}

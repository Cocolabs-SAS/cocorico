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
use Cocorico\CoreBundle\Entity\ListingCharacteristicValue;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadListingCharacteristicValueData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $listingCharacteristicValue = new ListingCharacteristicValue();
        $listingCharacteristicValue->translate('en')->setName('Yes');
        $listingCharacteristicValue->translate('fr')->setName('Oui');
        $listingCharacteristicValue->setPosition(1);
        /** @var ListingCharacteristicType $listingCharacteristicType */
        $listingCharacteristicType = $manager->merge($this->getReference('characteristic_type_yes_no'));
        $listingCharacteristicValue->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristicValue);
        $listingCharacteristicValue->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_value_yes', $listingCharacteristicValue);

        $listingCharacteristicValue = new ListingCharacteristicValue();
        $listingCharacteristicValue->translate('en')->setName('No');
        $listingCharacteristicValue->translate('fr')->setName('Non');
        $listingCharacteristicValue->setPosition(2);
        $listingCharacteristicValue->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristicValue);
        $listingCharacteristicValue->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_value_no', $listingCharacteristicValue);

        $listingCharacteristicValue = new ListingCharacteristicValue();
        $listingCharacteristicValue->translate('en')->setName('1');
        $listingCharacteristicValue->translate('fr')->setName('1');
        $listingCharacteristicValue->setPosition(1);
        $listingCharacteristicType = $manager->merge($this->getReference('characteristic_type_quantity'));
        $listingCharacteristicValue->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristicValue);
        $listingCharacteristicValue->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_value_1', $listingCharacteristicValue);

        $listingCharacteristicValue = new ListingCharacteristicValue();
        $listingCharacteristicValue->translate('en')->setName('2');
        $listingCharacteristicValue->translate('fr')->setName('2');
        $listingCharacteristicValue->setPosition(2);
        $listingCharacteristicValue->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristicValue);
        $listingCharacteristicValue->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_value_2', $listingCharacteristicValue);

        $listingCharacteristicValue = new ListingCharacteristicValue();
        $listingCharacteristicValue->translate('en')->setName('3');
        $listingCharacteristicValue->translate('fr')->setName('3');
        $listingCharacteristicValue->setPosition(3);
        $listingCharacteristicValue->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristicValue);
        $listingCharacteristicValue->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_value_3', $listingCharacteristicValue);

        $listingCharacteristicValue = new ListingCharacteristicValue();
        $listingCharacteristicValue->translate('en')->setName('Custom value 1');
        $listingCharacteristicValue->translate('fr')->setName('Valeur personnalisée 1');
        $listingCharacteristicValue->setPosition(1);
        $listingCharacteristicType = $manager->merge($this->getReference('characteristic_type_custom_1'));
        $listingCharacteristicValue->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristicValue);
        $listingCharacteristicValue->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_value_custom_1', $listingCharacteristicValue);

        $listingCharacteristicValue = new ListingCharacteristicValue();
        $listingCharacteristicValue->setName("Custom value 2");
        $listingCharacteristicValue->translate('en')->setName('Custom value 2');
        $listingCharacteristicValue->translate('fr')->setName('Valeur personnalisée 2');
        $listingCharacteristicValue->setPosition(2);
        $listingCharacteristicValue->setListingCharacteristicType($listingCharacteristicType);
        $manager->persist($listingCharacteristicValue);
        $listingCharacteristicValue->mergeNewTranslations();
        $manager->flush();
        $this->addReference('characteristic_value_custom_2', $listingCharacteristicValue);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }

}

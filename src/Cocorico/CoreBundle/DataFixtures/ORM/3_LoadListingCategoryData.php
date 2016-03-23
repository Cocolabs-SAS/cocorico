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

use Cocorico\CoreBundle\Entity\ListingCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadListingCategoryData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $category = new ListingCategory();
        $category->translate('en')->setName('Category1');
        $category->translate('fr')->setName('Categorie1');

        $subCategory1 = new ListingCategory();
        $subCategory1->translate('en')->setName('Category1_1');
        $subCategory1->translate('fr')->setName('Categorie1_1');
        $subCategory1->setParent($category);

        $subCategory2 = new ListingCategory();
        $subCategory2->translate('en')->setName('Category1_2');
        $subCategory2->translate('fr')->setName('Categorie1_2');
        $subCategory2->setParent($category);


        $manager->persist($category);
        $manager->persist($subCategory1);
        $manager->persist($subCategory2);
        $category->mergeNewTranslations();
        $subCategory1->mergeNewTranslations();
        $subCategory2->mergeNewTranslations();
        $manager->flush();
        $this->addReference('category1_1', $subCategory1);

        $category = new ListingCategory();
        $category->translate('en')->setName('Category2');
        $category->translate('fr')->setName('Categorie2');

        $subCategory1 = new ListingCategory();
        $subCategory1->translate('en')->setName('Category2_1');
        $subCategory1->translate('fr')->setName('Categorie2_1');
        $subCategory1->setParent($category);

        $subSubCategory1 = new ListingCategory();
        $subSubCategory1->translate('en')->setName('Category2_1_1');
        $subSubCategory1->translate('fr')->setName('Categorie2_1_1');
        $subSubCategory1->setParent($subCategory1);

        $subCategory2 = new ListingCategory();
        $subCategory2->translate('en')->setName('Category2_2');
        $subCategory2->translate('fr')->setName('Categorie2_2');
        $subCategory2->setParent($category);


        $manager->persist($category);
        $manager->persist($subCategory1);
        $manager->persist($subSubCategory1);
        $manager->persist($subCategory2);

        $category->mergeNewTranslations();
        $subCategory1->mergeNewTranslations();
        $subSubCategory1->mergeNewTranslations();
        $subCategory2->mergeNewTranslations();
        $manager->flush();

    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }

}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\DataTransformer;

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Entity\DirectoryListingCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

class DirectoryListingCategoriesToListingCategoriesTransformer implements DataTransformerInterface
{

    /**
     * @var Directory $directory
     */
    private $directory;

    /**
     * @param Directory $directory
     */
    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Used to convert data from the model to the normalized format.
     *
     * @param  DirectoryListingCategory[] $directoryListingCategories
     * @return array
     */
    public function transform($directoryListingCategories)
    {
        if (!is_null($directoryListingCategories)) {
            $results = [];

            foreach ($directoryListingCategories as $directoryListingCategory) {
                $results[] = $directoryListingCategory->getCategory();
            }

            return $results;
        }

        return $directoryListingCategories;
    }

    /**
     * Used to convert from the normalized to the model format
     *
     * @param  ListingCategory[] $listingCategories
     * @return mixed|null|object
     */
    public function reverseTransform($listingCategories)
    {
        $newDirectoryListingCategories = array();
        $directoryListingCategories = $this->directory->getDirectoryListingCategories();

        foreach ($listingCategories as $listingCategory) {
            $directoryListingCategory = new DirectoryListingCategory();
            $directoryListingCategory->setCategory($listingCategory);
            $directoryListingCategory->setDirectory($this->directory);
            $newDirectoryListingCategories[] = $directoryListingCategory;
        }

        //Check if listing has already one of the listingCategories setted above.
        //If yes the new listingCategories is replaced by the existing one
        /** @var DirectoryListingCategory $newDirectoryListingCategory */
        foreach ($newDirectoryListingCategories as $i => $newDirectoryListingCategory) {
            $directoryListingCategory = $this->exists($newDirectoryListingCategory, $directoryListingCategories);
            if ($directoryListingCategory) {
                //The new listingCategory is replaced by the existing one
                $newDirectoryListingCategories[$i] = $directoryListingCategory;
            }
        }

        //Check if some categories has been removed
        foreach ($directoryListingCategories as $i => $directoryListingCategory) {
            $categoryExists = $this->exists($directoryListingCategory, new ArrayCollection($newDirectoryListingCategories));
            if (!$categoryExists) {
                //Remove all related fields values
                if ($directoryListingCategory->getValues()) {
                    foreach ($directoryListingCategory->getValues() as $value) {
                        $value->getDirectoryCategoryListingCategoryField()->removeValue($value);
                        $value->getDirectoryListingCategory()->removeValue($value);
                    }
                }
                $this->directory->removeDirectoryListingCategory($directoryListingCategory);
            }
        }


        return $newDirectoryListingCategories;
    }


    /**
     * Check if category exist in collection
     *
     * @param DirectoryListingCategory[]|ArrayCollection $directoryListingCategories
     * @param DirectoryListingCategory                   $directoryListingCategory
     * @return DirectoryListingCategory|false
     */
    private function exists($directoryListingCategory, $directoryListingCategories)
    {
        /** @var  ArrayCollection $results */
        $results = $directoryListingCategories->filter(
            function (DirectoryListingCategory $element) use ($directoryListingCategory) {
                return $element->getCategory() === $directoryListingCategory->getCategory();
            }
        );

        if ($results->count()) {
            return $results->first();
        }

        return false;
    }

}

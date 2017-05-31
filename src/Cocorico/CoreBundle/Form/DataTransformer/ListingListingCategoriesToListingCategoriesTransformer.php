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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Entity\ListingListingCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

class ListingListingCategoriesToListingCategoriesTransformer implements DataTransformerInterface
{

    /**
     * @var Listing $listing
     */
    private $listing;

    /**
     * @param Listing $listing
     */
    public function __construct(Listing $listing)
    {
        $this->listing = $listing;
    }

    /**
     * Used to convert data from the model to the normalized format.
     *
     * @param  ListingListingCategory[] $listingListingCategories
     * @return ArrayCollection
     */
    public function transform($listingListingCategories)
    {
        if (!is_null($listingListingCategories)) {
            $results = [];

            foreach ($listingListingCategories as $listingListingCategory) {
                $results[] = $listingListingCategory->getCategory();
            }

            return $results;
        }

        return $listingListingCategories;
    }

    /**
     * Used to convert from the normalized to the model format
     *
     * @param  ListingCategory[] $listingCategories
     * @return mixed|null|object
     */
    public function reverseTransform($listingCategories)
    {
        $newListingListingCategories = array();
        $listingListingCategories = $this->listing->getListingListingCategories();

        foreach ($listingCategories as $listingCategory) {
            $listingListingCategory = new ListingListingCategory();
            $listingListingCategory->setCategory($listingCategory);
            $listingListingCategory->setListing($this->listing);
            $newListingListingCategories[] = $listingListingCategory;
        }

        //Check if listing has already one of the listingCategories setted above.
        //If yes the new listingCategories is replaced by the existing one
        /** @var ListingListingCategory $newListingListingCategory */
        foreach ($newListingListingCategories as $i => $newListingListingCategory) {
            $listingListingCategory = $this->exists($newListingListingCategory, $listingListingCategories);
            if ($listingListingCategory) {
                //The new listingCategory is replaced by the existing one
                $newListingListingCategories[$i] = $listingListingCategory;
            }
        }

        //Check if some categories has been removed
        foreach ($listingListingCategories as $i => $listingListingCategory) {
            $categoryExists = $this->exists($listingListingCategory, new ArrayCollection($newListingListingCategories));
            if (!$categoryExists) {
                //Remove all related fields values
                if ($listingListingCategory->getValues()) {
                    foreach ($listingListingCategory->getValues() as $value) {
                        $value->getListingCategoryListingCategoryField()->removeValue($value);
                        $value->getListingListingCategory()->removeValue($value);
                    }
                }
                $this->listing->removeListingListingCategory($listingListingCategory);
            }
        }


        return $newListingListingCategories;
    }


    /**
     * Check if category exist in collection
     *
     * @param ListingListingCategory[]|ArrayCollection $listingListingCategories
     * @param ListingListingCategory                   $listingListingCategory
     * @return ListingListingCategory|false
     */
    private function exists($listingListingCategory, $listingListingCategories)
    {
        /** @var  ArrayCollection $results */
        $results = $listingListingCategories->filter(
            function (ListingListingCategory $element) use ($listingListingCategory) {
                return $element->getCategory() === $listingListingCategory->getCategory();
            }
        );

        if ($results->count()) {
            return $results->first();
        }

        return false;
    }

}

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

use Cocorico\CoreBundle\Entity\ListingListingCategory;


/**
 * ListingCategoryFieldValueInterface
 */
interface ListingCategoryFieldValueInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * @return ListingCategoryListingCategoryFieldInterface
     */
    public function getListingCategoryListingCategoryField();

    /**
     * @param ListingCategoryListingCategoryFieldInterface $field
     */
    public function setListingCategoryListingCategoryField(ListingCategoryListingCategoryFieldInterface $field);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $value
     */
    public function setValue($value = null);

    /**
     * @return ListingListingCategory
     */
    public function getListingListingCategory();

    /**
     * @param ListingListingCategory $listingListingCategory
     */
    public function setListingListingCategory(ListingListingCategory $listingListingCategory);
}
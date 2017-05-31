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

use Cocorico\CoreBundle\Entity\ListingCategory;


/**
 * ListingCategoryListingCategoryFieldInterface
 *
 */
interface ListingCategoryListingCategoryFieldInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * @return ListingCategoryFieldInterface
     */
    public function getField();

    /**
     * @param ListingCategoryFieldInterface $field
     */
    public function setField(ListingCategoryFieldInterface $field);

    /**
     * @return ListingCategory
     */
    public function getCategory();

    /**
     * @param ListingCategory $category
     */
    public function setCategory(ListingCategory $category);

    /**
     * Add field value
     *
     * @param ListingCategoryFieldValueInterface $value
     * @return ListingCategoryListingCategoryFieldInterface
     */
    public function addValue(ListingCategoryFieldValueInterface $value);

    /**
     * Remove field value
     *
     * @param  ListingCategoryFieldValueInterface $value
     */
    public function removeValue(ListingCategoryFieldValueInterface $value);

    /**
     * Get field values
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|ListingCategoryFieldValueInterface[]
     */
    public function getValues();
}
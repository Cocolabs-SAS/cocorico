<?php

namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Entity\DirectoryListingCategory;


/**
 * DirectoryCategoryFieldValueInterface
 */
interface DirectoryCategoryFieldValueInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * @return DirectoryCategoryListingCategoryFieldInterface
     */
    public function getDirectoryCategoryListingCategoryField();

    /**
     * @param DirectoryCategoryListingCategoryFieldInterface $field
     */
    public function setDirectoryCategoryListingCategoryField(ListingCategoryListingCategoryFieldInterface $field);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $value
     */
    public function setValue($value = null);

    /**
     * @return DirectoryListingCategory
     */
    public function getDirectoryListingCategory();

    /**
     * @param DirectoryListingCategory $listingListingCategory
     */
    public function setDirectoryListingCategory(DirectoryListingCategory $DirectoryListingCategory);
}

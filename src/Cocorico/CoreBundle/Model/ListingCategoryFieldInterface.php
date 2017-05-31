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

use Doctrine\Common\Collections\ArrayCollection;


/**
 * ListingCategoryFieldInterface
 *
 */
interface ListingCategoryFieldInterface
{
    /**
     * @return int
     */
    public function getType();

    /**
     * Set type
     *
     * @param  integer $type
     * @return ListingCategoryFieldInterface
     */
    public function setType($type);

    /**
     * Get Type Text
     *
     * @return string
     */
    public function getTypeText();

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $position
     */
    public function setPosition($position);

    /**
     * @return boolean
     */
    public function getSearchable();

    /**
     * @param boolean $searchable
     */
    public function setSearchable($searchable);

    /**
     * Translation proxy
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments);

    /**
     * @return int
     */
    public function getId();

    /**
     * Add categories field relation
     *
     * @param ListingCategoryListingCategoryFieldInterface $listingCategoryListingCategoryField
     * @return ListingCategoryFieldInterface
     */
    public function addListingCategoryListingCategoryField(
        ListingCategoryListingCategoryFieldInterface $listingCategoryListingCategoryField
    );

    /**
     * @param ArrayCollection $listingCategoryListingCategoryFields
     */
    public function setListingCategoryListingCategoryFields(ArrayCollection $listingCategoryListingCategoryFields);

    /**
     * Remove categories field relation
     *
     * @param ListingCategoryListingCategoryFieldInterface $listingCategoryListingCategoryField
     * @return ListingCategoryFieldInterface
     */
    public function removeListingCategoryListingCategoryField(
        ListingCategoryListingCategoryFieldInterface $listingCategoryListingCategoryField
    );

    /**
     * Get categories fields relations
     *
     * @return \Doctrine\Common\Collections\Collection|ListingCategoryListingCategoryFieldInterface
     */
    public function getListingCategoryListingCategoryFields();

    /**
     * @return mixed
     */
    public function getGroup();

    /**
     * @param mixed $group
     */
    public function setGroup($group);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getPlaceHolder();

    /**
     * @return string
     */
    public function __toString();
}
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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ListingSearchRequest
 *
 * Represent the listing search request
 *
 */
class DirectorySearchRequest
{
    protected $categories;
    protected $format;
    protected $structureType;
    protected $prestaType;
    protected $withAntenna;
    protected $address;
    protected $lat;
    protected $lng;
    protected $country;
    protected $area;
    protected $department;
    protected $city;
    protected $postalCode;
    protected $zip;
    protected $addressType;

    /**
     * @param RequestStack $requestStack
     * @param int          $maxPerPage
     */
    public function __construct(RequestStack $requestStack, $maxPerPage)
    {
        //Params
        $this->requestStack = $requestStack;
        $this->request = $this->requestStack->getCurrentRequest();
        if ($this->request) {
            if ($this->request->isXmlHttpRequest()) {
                $this->isXmlHttpRequest = true;
            }
        }

        $this->maxPerPage = $maxPerPage;
        $this->page = 1;


        //Categories
        $this->categories = array();
        $categories = $this->request->query->get("categories");
        if (is_array($categories)) {
            $this->categories = $categories;
        }

        //Categories fields
        $this->categoriesFields = array();
        $categoriesFields = $this->request->query->get("categories_fields");
        if (is_array($categoriesFields)) {
            $this->categoriesFields = $categoriesFields;
        }

    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param mixed $sortBy
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @param mixed $maxPerPage
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * @return boolean
     */
    public function getIsXmlHttpRequest()
    {
        return $this->isXmlHttpRequest;
    }

    /**
     * @param boolean $isXmlHttpRequest
     */
    public function setIsXmlHttpRequest($isXmlHttpRequest)
    {
        $this->isXmlHttpRequest = $isXmlHttpRequest;
    }

    public function getStructureType()
    {
        return $this->structureType;
    }

    public function setStructureType($structureType)
    {
        return $this->structureType = $structureType;
    }

    public function getPrestaType()
    {
        return $this->prestaType;
    }

    public function setPrestaType($prestaType)
    {
        return $this->prestaType = $prestaType;
    }

    public function getWithAntenna()
    {
        return $this->withAntenna;
    }

    public function setWithAntenna($withAntenna)
    {
        return $this->withAntenna = $withAntenna;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        return $this->address = $address;
    }




    /**
     * Remove some Object properties while serialisation
     *
     * @return array
     */
    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), array('requestStack', 'request'));
    }
}

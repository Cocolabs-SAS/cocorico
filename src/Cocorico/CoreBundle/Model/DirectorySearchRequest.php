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
    protected $sectors;
    protected $serialSectors;
    protected $format;
    protected $structureType;
    protected $prestaType;
    protected $withAntenna;
    protected $withRange;
    protected $address;
    protected $lat;
    protected $lng;
    protected $country;
    protected $area;
    protected $department;
    protected $region;
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


        //Sectors (old categories)
        $this->sectors = array();
        $sectors = $this->request->query->get("sector");
        $serialSectors = $this->request->query->get("serialSectors");
        if (is_array($sectors)) {
            $this->sectors = $sectors;
        } else if ($serialSectors) {
            $this->sectors = explode('|', $serialSectors);
        }

        $format = $this->request->query->get("format");
        if ($format) {
            $this->format = $format;
        }

        $prestaType = $this->request->query->get("prestaType");
        if ($prestaType) {
            $this->prestaType = $prestaType;
        }

        $withAntenna = $this->request->query->get("withAntenna");
        if ($withAntenna) {
            $this->withAntenna = $withAntenna == "1";
        }

        $withRange = $this->request->query->get("withRange");
        if ($withRange) {
            $this->withRange = $withRange == "1";
        }


        $postalCode = $this->request->query->get("postalCode");
        $zip = $this->request->query->get("zip");
        if ($postalCode) {
            $this->postalCode = $postalCode;
        } else if ($zip) {
            $this->postalCode = $zip;
        }

        $region = $this->request->query->get("region");
        if ($region) {
            $this->region = $region;
        }

        $type = $this->request->query->get("type");
        if ($type) {
            $this->type = $type;
        }

    }

    public function getKeyValue($prop) {
        return $this->$prop;
    }

    /**
     * @return mixed
     */
    public function getSectors()
    {
        return $this->sectors;
    }

    /**
     * @param mixed $sectors
     */
    public function setSectors($sectors)
    {
        $this->sectors = $sectors;
    }

    /**
     * @return mixed
     */
    public function getSerialSectors()
    {
        return $this->serialSectors;
    }

    /**
     * @param mixed $serialSectors
     */
    public function setSerialSectors($serialSectors)
    {
        $this->serialSectors = $serialSectors;
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
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
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

    public function getWithRange()
    {
        return $this->withRange;
    }

    public function setWithRange($withRange)
    {
        return $this->withRange = $withRange;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        return $this->address = $address;
    }


    public function getLat()
    {
        return $this->lat;
    }

    public function setLat($lat)
    {
        return $this->lat = $lat;
    }

    public function getLng()
    {
        return $this->lng;
    }

    public function setLng($lng)
    {
        return $this->lng = $lng;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        return $this->country = $country;
    }

    public function getArea()
    {
        return $this->area;
    }

    public function setArea($area)
    {
        return $this->area = $area;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function setDepartment($department)
    {
        return $this->department = $department;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion($region)
    {
        return $this->region = $region;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        return $this->city = $city;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode($postalCode)
    {
        return $this->postalCode = $postalCode;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($zip)
    {
        return $this->zip = $zip;
    }

    public function getAddressType()
    {
        return $this->addressType;
    }

    public function setAddressType($addressType)
    {
        return $this->addressType = $addressType;
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

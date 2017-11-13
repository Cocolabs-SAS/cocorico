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

use Symfony\Component\Intl\Intl;

/**
 * Class ListingLocationSearchRequest
 *
 * Represent the listing location search request
 *
 */
class ListingLocationSearchRequest
{
    protected $lat;
    protected $lng;
    protected $country;
    protected $countryText;
    protected $area;
    protected $department;
    protected $city;
    protected $zip;
    protected $route;
    protected $streetNumber;
    protected $address;
    protected $viewport;
    protected $addressType;
    protected $locale;

    /**
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param mixed $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
        $this->countryText = Intl::getRegionBundle()->getCountryName($country, $this->locale);
    }

    /**
     * @return mixed
     */
    public function getCountryText()
    {
        return $this->countryText;
    }

    /**
     * @param mixed $countryText
     */
    public function setCountryText($countryText)
    {
        $this->countryText = $countryText;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param mixed $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param mixed $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return mixed
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * @param mixed $streetNumber
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getViewport()
    {
        return $this->viewport;
    }

    /**
     * @param mixed $viewport
     */
    public function setViewport($viewport)
    {
        $this->viewport = $viewport;
    }

    /**
     * @return mixed
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * @param string $addressType
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;
    }


    /**
     * Get bound coordinate from viewport string
     *
     * @param  string $viewport e: ((48.815573, 2.2241989999999987), (48.9021449, 2.4699207999999544))
     * @return array|null
     */
    public function getBound($viewport = null)
    {
        $viewport = $viewport ? $viewport : $this->getViewport();
        if (!$viewport) {
            return null;
        }

        preg_match('/\(\((.*?)\), \((.*?)\)\)/', $viewport, $matches);
        if (count($matches) == 3) {
            $sw = $ne = null;

            $coordinate = explode(',', $matches[1]);
            if (count($coordinate) == 2) {
                $latitude = floatval(trim($coordinate[0]));
                $longitude = floatval(trim($coordinate[1]));

                $sw = array("lat" => $latitude, "lng" => $longitude);
            }

            $coordinate = explode(',', $matches[2]);
            if (count($coordinate) == 2) {
                $latitude = floatval(trim($coordinate[0]));
                $longitude = floatval(trim($coordinate[1]));

                $ne = array("lat" => $latitude, "lng" => $longitude);
            }

            if (!is_null($sw) && !is_null($ne)) {
                return array("sw" => $sw, "ne" => $ne);
            }
        }

        return null;
    }

    /**
     * Return the most precise location searched
     *
     * @return string
     */
    public function getAccuracy()
    {
        if ($this->getRoute()) {
            return 'route';
        } elseif ($this->getZip()) {
            return 'zip';
        } elseif ($this->getCity()) {
            return 'city';
        } elseif ($this->getDepartment()) {
            return 'department';
        } elseif ($this->getArea()) {
            return 'area';
        } elseif ($this->getCountry()) {
            return 'country';
        } else {
            return false;
        }
    }

    /**
     * Convert geo provider address type to a simplest address type
     * For Google provider : see https://developers.google.com/maps/documentation/javascript/geocoding
     *
     * @return string
     */
    public function getSimplifiedAddressType()
    {
        $simplifiedAddressType = "";
        $addressType = $this->getAddressType();

        if (stripos($addressType, 'street_address') !== false
            || stripos($addressType, 'route') !== false
            || stripos($addressType, 'intersection') !== false
            || stripos($addressType, 'premise') !== false
            || stripos($addressType, 'park') !== false
            || stripos($addressType, 'establishment') !== false
            || stripos($addressType, 'transit_station') !== false
        ) {
            $simplifiedAddressType = "route";
        } elseif (stripos($addressType, 'postal_code') !== false
            || stripos($addressType, 'sublocality') !== false
            || stripos($addressType, 'neighborhood') !== false
        ) {
            $simplifiedAddressType = "zip";
        } elseif (stripos($addressType, 'locality') !== false) {
            $simplifiedAddressType = "city";
        } elseif (stripos($addressType, 'administrative_area_level_') !== false) {
            if (stripos($addressType, 'administrative_area_level_1') !== false) {
                $simplifiedAddressType = "area";
            } else {
                $simplifiedAddressType = "department";
            }
        } elseif (stripos($addressType, 'country') !== false) {
            $simplifiedAddressType = "country";
        }

        return $simplifiedAddressType;
    }

    /**
     * Return the address of the parent level of current location
     *
     * @return array|false
     *
     */
    public function getParentLocation()
    {
        $parentLocation = array("address" => "", "type" => "");
        $parentAddressType = "";
        $addressType = $this->getSimplifiedAddressType();

        if ($addressType == 'route') {//If current address is route
            if ($this->getZip()) {//and have zip
                $parentAddressType = 'zip';//parent address type is zip
            } else {//Google sometimes don't send zip code of route if there is no street number
                if (!$this->getStreetNumber()) {
                    $parentAddressType = 'route';
                    $this->setStreetNumber(1);
                } elseif ($this->getCity()) {
                    $parentAddressType = 'city';//parent address type is city
                } elseif ($this->getDepartment()) {
                    $parentAddressType = 'department';
                } elseif ($this->getArea()) {
                    $parentAddressType = 'area';
                } elseif ($this->getCountry()) {
                    $parentAddressType = 'country';
                }
            }
        } elseif ($addressType == 'zip') {
            if ($this->getCity()) {
                $parentAddressType = 'city';
            } elseif ($this->getDepartment()) {
                $parentAddressType = 'department';
            } elseif ($this->getArea()) {
                $parentAddressType = 'area';
            } elseif ($this->getCountry()) {
                $parentAddressType = 'country';
            }
        } elseif ($addressType == 'city') {
            if ($this->getDepartment() && $this->getDepartment() != $this->getCity()) {
                $parentAddressType = 'department';
            } elseif ($this->getArea()) {//City has the same name than department or no department
                $parentAddressType = 'area';
            } elseif ($this->getCountry()) {
                $parentAddressType = 'country';
            }
        } elseif ($addressType == 'department') {
            if ($this->getArea()) {
                $parentAddressType = 'area';
            } elseif ($this->getCountry()) {
                $parentAddressType = 'country';
            }
        } elseif ($addressType == 'area') {
            if ($this->getCountry()) {
                $parentAddressType = 'country';
            }
        }


        //Construct parent address of current address
        if ($parentAddressType) {
            $parentLocation["type"] = $parentAddressType;

            switch ($parentAddressType) {
                case 'route':
                    $parentLocation["address"] = $this->getStreetNumber() . " " . $this->getRoute() . "##|##" .
                        $this->getCity() . "##|##" . $this->getCountryText();
                    break;
                case 'zip':
                    $parentLocation["address"] = $this->getZip() . "##|##" . $this->getCity() . "##|##" .
                        $this->getCountryText();
                    break;
                case 'city':
                    $parentLocation["address"] = $this->getCity() . "##|##" . $this->getCountryText();
                    break;
                case 'department':
                    $parentLocation["address"] = $this->getDepartment() . "##|##" . $this->getCountryText();
//                    $parentLocation["address"] = $this->getDepartment() . "##|##" . $this->getArea() . "##|##"
//                        . $this->getCountryText();
                    break;
                case 'area':
                    $parentLocation["address"] = $this->getArea() . "##|##" . $this->getCountryText();
                    break;
                case 'country':
                    $parentLocation["address"] = $this->getCountryText();
                    break;
            }


            //Special case where parent address is the same that current address.
            //In this case the parent address is replaced by country
            if (str_replace(array("##|##", ", ", ","), " ", $parentLocation["address"]) ==
                str_replace(array(", ", ","), " ", $this->getAddress())
            ) {
                //Remove first part of the search string
                $parentAddress = substr(strstr($parentLocation["address"], "##|##"), 5);
                $parentLocation["address"] = $parentAddress;
            }
            //Delete separator string at the beginning if any
            if (substr($parentLocation["address"], 0, 5) == "##|##") {
                $parentLocation["address"] = substr($parentLocation["address"], 5);
            }

            $parentLocation["address"] = str_replace("##|##", ", ", $parentLocation["address"]);
        }

        return $parentLocation;
    }

    public function getAccuracyMethod()
    {
        if ($accuracy = $this->getAccuracy()) {
            return "get" . ucfirst($accuracy);
        }

        return false;
    }

    /**
     * Return the Coordinate entity fields mapping for a given accuracy
     *
     * @return array|null
     */
    public function getAccuracyMapping()
    {
        switch ($this->getAccuracy()) {
            case 'route':
                return array(
                    'table' => '',
                    'field' => 'route'
                );
            case 'zip':
                return array(
                    'table' => '',
                    'field' => 'zip'
                );
            case 'city':
                return array(
                    'table' => 'city',
                    'field' => 'name',
                );
            case 'department':
                return array(
                    'table' => 'department',
                    'field' => 'name',
                );
            case 'area':
                return array(
                    'table' => 'area',
                    'field' => 'name',
                );
            case 'country':
                return array(
                    'table' => 'country',
                    'field' => 'name',
                );
            default:
                return false;
        }
    }


    /**
     * Get viewport diagonal distance in km
     *
     * @param  string $viewport e: ((48.815573, 2.2241989999999987), (48.9021449, 2.4699207999999544))
     *
     * @return float
     */
    public function getViewportDiagonalDistance($viewport = null)
    {
        $_piBy180 = 0.0174532925; //PI() / 180
        $_180byPi = 57.2957795131; //PI() / 180

        $bounds = $this->getBound($viewport);
        if (!$bounds) {
            return 0;
        }

        $neLat = $bounds["ne"]["lat"];
        $neLng = $bounds["ne"]["lng"];
        $swLat = $bounds["sw"]["lat"];
        $swLng = $bounds["sw"]["lng"];

        $distance = (sin($neLat * $_piBy180) * sin($swLat * $_piBy180)) +
            (cos($neLat * $_piBy180) * cos($swLat * $_piBy180) * cos(($neLng - $swLng) * $_piBy180));
        $distance = acos($distance);
        $distance = $distance * $_180byPi;
        $distance = $distance * 60 * 1.1515 * 1.609344;//in km

        return $distance;
    }
}

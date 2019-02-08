<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\Form\DataTransformer;

use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Coordinate;
use Cocorico\GeoBundle\Entity\Country;
use Cocorico\GeoBundle\Entity\Department;
use Cocorico\GeoBundle\Geocoder\Provider\GoogleMaps;
use Cocorico\GeoBundle\Geocoder\StatefulGeocoder;
use Doctrine\Common\Persistence\ObjectManager;
use Http\Adapter\Guzzle6\Client;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GeocodingToCoordinateEntityTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;
    /**
     * @var array
     */
    private $locales;
    private $locale;
    private $googlePlaceAPIKey;

    /**
     * @var bool
     */
    const DEBUG = false;

    /**
     * @param ObjectManager $om
     * @param array         $locales
     * @param string        $locale
     * @param string        $googlePlaceAPIKey
     */
    public function __construct(ObjectManager $om, $locales, $locale, $googlePlaceAPIKey = null)
    {
        $this->om = $om;
        $this->locales = $locales;
        $this->locale = $locale;
        $this->googlePlaceAPIKey = $googlePlaceAPIKey ? $googlePlaceAPIKey : null;
    }

    /**
     * Transforms an object (coordinate) to a string (number).
     *
     * @param  Coordinate|null $coordinate
     * @return string
     */
    public function transform($coordinate)
    {
        if (null === $coordinate) {
            return "";
        }

        return "";
    }

    /**
     * Transforms a JSON string (geocoding) to an object (coordinate).
     *
     * @param  string $geocodingI18n json Geocoding in multi languages
     * @return Coordinate|null
     * @throws TransformationFailedException if object (coordinate) is not found.
     */
    public function reverseTransform($geocodingI18n)
    {
        if (!$geocodingI18n) {
            throw new TransformationFailedException();
        }
        $this->debug("reverseTransform > geocodingI18n :\n" . print_r($geocodingI18n, 1));

        // wrap numbers
        $geocodingI18n = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $geocodingI18n);
        $geocodingI18n = json_decode($geocodingI18n);

        $this->debug("reverseTransform > geocodingI18n JSON decoded :\n" . print_r($geocodingI18n, 1));

        if (!$geocodingI18n->{$this->locale}) {
            $this->debug("reverseTransform > Error: No geocodingI18n locale.\n");
            throw new TransformationFailedException();
        }

        $lat = $geocodingI18n->lat;
        $lng = $geocodingI18n->lng;

        //Geocoding server side
        $geocodingI18nServer = $this->getGeocodingServer(
            $geocodingI18n->{$this->locale}->country_short,
            $lat,
            $lng
        );

        //Success? The server data are used.
        if ($geocodingI18nServer) {
            $geocodingI18n = $geocodingI18nServer;
            $this->debug("reverseTransform > geocodingI18n server done :\n" . print_r($geocodingI18n, 1));
        }

        //Current Locale Geocoding
        $geocoding = $this->getGeocoding($geocodingI18n);
        if (!$geocoding) {
            $this->debug("reverseTransform > Error: No geocoding.");
            throw new TransformationFailedException();
        }

        //JSON and DB lat and lng must be compared with the same precision:
        //ex : 48.869782|2.3508079000000635 (JSON) != 48.8697820|2.3508079 (DB)
        $coordinate = $this->om->getRepository('CocoricoGeoBundle:Coordinate')->findOneBy(
            array(
                'lat' => number_format($lat, 7, '.', ''),
                'lng' => number_format($lng, 7, '.', ''),
            )
        );

        $this->debug("reverseTransform > LatLng:\n" . $lat . "--" . $lng);
//        die();

        if (null === $coordinate) {
            try {
                $route = (isset($geocoding->route) && $geocoding->route) ? $geocoding->route : 'noroute';
                $streetNumber = (isset($geocoding->street_number) && $geocoding->street_number) ?
                    $geocoding->street_number : 'nostreetnumber';
                $zip = (isset($geocoding->postal_code) && $geocoding->postal_code) ? $geocoding->postal_code :
                    (isset($geocoding->administrative_area_level_2_short) && $geocoding->administrative_area_level_2_short ?
                        $geocoding->administrative_area_level_2_short : '');

                //Coordinate
                $coordinate = new Coordinate();
                $coordinate->setLat($lat);
                $coordinate->setLng($lng);
                $coordinate->setRoute($route);
                $coordinate->setStreetNumber($streetNumber);
                $coordinate->setZip($zip);
                $coordinate->setAddress($geocodingI18n->formatted_address);
                $geographicalEntities = $this->getGeographicalEntities($geocodingI18n);
                $coordinate->setCountry($geographicalEntities["country"]);
                $coordinate->setArea($geographicalEntities["area"]);
                $coordinate->setDepartment($geographicalEntities["department"]);
                $coordinate->setCity($geographicalEntities["city"]);

            } catch (\Exception $e) {
                throw new TransformationFailedException();
            }

        }

        return $coordinate;
    }

    /**
     *
     * Get geocoding server datas
     *
     * @param $region
     * @param $lat
     * @param $lng
     * @return bool|mixed
     * @throws TransformationFailedException
     */
    private function getGeocodingServer($region, $lat, $lng)
    {
        //Server geocoding. If we can we use it instead of client geocoding
        try {
            $geocodingsServer = $geocodingI18nServer = array();

            $httpClient = new Client();
            $provider = new GoogleMaps($httpClient, $region, $this->googlePlaceAPIKey);

            //Geocoding for each locale
            foreach ($this->locales as $locale) {
                $geocoder = new StatefulGeocoder($provider, $locale);
                $geocoder->setLimit(1);
                $geocodingsServers = $geocoder->reverse($lat, $lng)->all();
                $geocodingsServer[$locale] = $geocodingsServers[0];
            }

            //Move current locale to end of array. The current locale is the reference.
            $localeGeocoding = $geocodingsServer[$this->locale];
            unset($geocodingsServer[$this->locale]);
            $geocodingsServer[$this->locale] = $localeGeocoding;

            $this->debug("getGeocodingServer > geocodingsServer:\n" . print_r($geocodingsServer, 1));

            //Merge them
            foreach ($geocodingsServer as $geocodingServer) {
                $geocodingI18nServer = array_merge($geocodingI18nServer, $geocodingServer);
            }

        } catch (\Exception $e) {
            $this->debug("getGeocodingServer NoResult Error:\n" . $e->getMessage());
            throw new TransformationFailedException();
        }

        //Replace the client geocoding by the server one
        if ($geocodingI18nServer) {
            return json_decode(json_encode($geocodingI18nServer));
        } else {
            return false;
        }
    }

    /**
     * Get and/or set Geographical Entities from geocoding information
     *
     * @param $geocodingI18n
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getGeographicalEntities($geocodingI18n)
    {
        //Translations
        $countries = $areas = $departments = $cities = array();
        foreach ($this->locales as $locale) {
            $geoLocale = $geocodingI18n->{$locale};
            //Country
            $countries[$locale] = $geoLocale->country;
            //Area
            $areas[$locale] =
                (isset($geoLocale->administrative_area_level_1) && $geoLocale->administrative_area_level_1) ?
                    $geoLocale->administrative_area_level_1 :
                    (
                    (isset($geoLocale->administrative_area_level_2) && $geoLocale->administrative_area_level_2) ?
                        $geoLocale->administrative_area_level_2 : 'noarea'
                    );
            //Department
            $departments[$locale] = (
                isset($geoLocale->administrative_area_level_2) && $geoLocale->administrative_area_level_2) ?
                $geoLocale->administrative_area_level_2 : 'nodepartment';

            //City
            $cities[$locale] = (
                isset($geoLocale->locality) && $geoLocale->locality) ? $geoLocale->locality :
                (
                (isset($geoLocale->postal_town) && $geoLocale->postal_town) ? $geoLocale->postal_town : 'nozip'
                );
        }

        $country = $this->getOrCreateCountry($geocodingI18n->{$this->locale}->country_short, $countries);
        $area = $this->getOrCreateArea($areas, $country);
        $department = $this->getOrCreateDepartment($departments, $area);
        $city = $this->getOrCreateCity($cities, $department);

        return array(
            "country" => $country,
            "area" => $area,
            "department" => $department,
            "city" => $city,
        );

    }

    /**
     * Verify if a geocoding is valid and return geocoding for current locale
     *
     * @param  string $geocodingI18n (json)
     * @return bool|object
     */
    private function getGeocoding($geocodingI18n)
    {
        //Geocoding in current locale
        if (!isset($geocodingI18n->{$this->locale})) {
            return false;
        }
        $geocoding = $geocodingI18n->{$this->locale};
        if (!isset($geocodingI18n->lat) || !isset($geocodingI18n->lng) ||
            !isset($geocoding->country_short) || !isset($geocodingI18n->formatted_address) ||
            (
                !isset($geocoding->administrative_area_level_1) && !isset($geocoding->administrative_area_level_2)
            )
        ) {
            return false;
        }

        return $geocoding;
    }

    /**
     * Get or create and translate country
     *
     * @param  string $code
     * @param  array  $names
     * @return Country
     */
    private function getOrCreateCountry($code, $names)
    {
        $country = $this->om->getRepository('CocoricoGeoBundle:Country')->findOneBy(
            array(
                'code' => $code
            )
        );

        if (null === $country) {
            $country = new Country();
            $country->setCode($code);
            foreach ($names as $lang => $name) {
                $country->translate($lang)->setName($name);
            }

            $this->om->persist($country);
            $country->mergeNewTranslations();
        }

        return $country;
    }

    /**
     * Get or create and translate area
     *
     * @param  array   $names
     * @param  Country $country
     * @return Area|mixed|null
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getOrCreateArea($names, $country)
    {
        $area = null;
        if ($country->getId()) {
            $area = $this->om->getRepository('CocoricoGeoBundle:Area')->findOneByNameAndCountry(
                $names[$this->locale],
                $country
            );
        }

        if (null === $area) {
            $area = new Area();
            $area->setCountry($country);
            foreach ($names as $lang => $name) {
                $area->translate($lang)->setName($name);
            }

            $this->om->persist($area);
            $area->mergeNewTranslations();
        }

        return $area;
    }

    /**
     ** Get or create and translate department
     *
     * @param  array $names
     * @param  Area  $area
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getOrCreateDepartment($names, $area)
    {
        $department = null;
        if ($area->getId()) {
            $department = $this->om->getRepository('CocoricoGeoBundle:Department')->findOneByNameAndArea(
                $names[$this->locale],
                $area
            );
        }

        if (null === $department) {
            $department = new Department();
            $department->setCountry($area->getCountry());
            $department->setArea($area);
            foreach ($names as $lang => $name) {
                $department->translate($lang)->setName($name);
            }

            $this->om->persist($department);
            $department->mergeNewTranslations();
        }

        return $department;
    }

    /**
     * Get or create and translate city
     *
     * @param  array      $names
     * @param  Department $department
     * @return City
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getOrCreateCity($names, $department)
    {
        $city = null;
        if ($department->getId()) {
            $city = $this->om->getRepository('CocoricoGeoBundle:City')->findOneByNameAndDepartment(
                $names[$this->locale],
                $department
            );
        }

        if (null === $city) {
            $city = new City();
            $city->setCountry($department->getCountry());
            $city->setArea($department->getArea());
            $city->setDepartment($department);
            foreach ($names as $lang => $name) {
                $city->translate($lang)->setName($name);
            }
            $this->om->persist($city);
            $city->mergeNewTranslations();
        }

        return $city;
    }

    /**
     * @param $message
     */
    private function debug($message)
    {
        if (self::DEBUG) {
            echo '<pre>' . nl2br($message) . "</pre><br>";
        }
    }
}

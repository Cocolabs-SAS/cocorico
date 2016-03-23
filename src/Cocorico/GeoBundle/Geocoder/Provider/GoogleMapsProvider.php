<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\Geocoder\Provider;

use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\QuotaExceededException;


class GoogleMapsProvider extends \Geocoder\Provider\GoogleMapsProvider
{
    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $query = $this->buildQuery($query);
        $content = $this->getAdapter()->getContent($query);

//        die($content);
        // Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
        if (strpos($content, "Provided 'signature' is not valid for the provided client ID") !== false) {
            throw new InvalidCredentialsException(sprintf('Invalid client ID / API Key %s', $query));
        }

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        //echo "Content executeQuery :" . $content;

        $content = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $content);
        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if ('REQUEST_DENIED' === $json->status && 'The provided API key is invalid.' === $json->error_message) {
            throw new InvalidCredentialsException(sprintf('API key is invalid %s', $query));
        }

        // you are over your quota
        if ('OVER_QUERY_LIMIT' === $json->status) {
            throw new QuotaExceededException(sprintf('Daily quota exceeded %s', $query));
        }

        // no result
        if (!isset($json->results) || !count($json->results) || 'OK' !== $json->status) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $results = array();

        //This format is also used on client side (@see geocoding_js.html.twig).
        //It must be the same on both sides
        foreach ($json->results as $i => $result) {
            $resultSet = array();
            // update address components
            foreach ($result->address_components as $component) {
                foreach ($component->types as $name) {
                    $resultSet[$this->getLocale()][$name] = $component->long_name;
                    $resultSet[$this->getLocale()][$name . "_short"] = $component->short_name;
                }
            }

            // update coordinates
            $resultSet['formatted_address'] = $result->formatted_address;
            $geometry = $result->geometry;
            $resultSet['location_type'] = $geometry->location_type ? $geometry->location_type : "PLACES";
            $viewport = $geometry->viewport;
            $resultSet['viewport'] = $viewport;

            $resultSet['bounds'] = null;
            if (isset($geometry->bounds)) {
                $resultSet['bounds'] = $geometry->bounds;
            }
            $location = $geometry->location;
            $resultSet['location'] = $location;
            $resultSet['lat'] = $location->lat;
            $resultSet['lng'] = $location->lng;

            $results = $resultSet;
//            echo "<pre>";
//            print_r($resultSet);
//            echo "</pre>";

            //Sometimes google change the order of its results for the same request and  send as first result
            //a place with less informations than the next result.
            //So in this case we the following result.
            //Same checking is done in GeocodingToCoordinateEntityTransformer->getGeocoding($geocodingI18n)
            if (
                !isset($results[$this->getLocale()]) ||
                !isset($results[$this->getLocale()]["country_short"]) ||
                !isset($results['formatted_address']) ||
                (
                    !isset($results[$this->getLocale()]["administrative_area_level_1"]) &&
                    !isset($results[$this->getLocale()]["administrative_area_level_2"])
                )
            ) {
                continue;
            } else {
                break; //Only first
            }
        }

        return $results;
    }

}

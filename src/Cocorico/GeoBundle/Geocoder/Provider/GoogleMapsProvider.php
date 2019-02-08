<?php
//
///*
// * This file is part of the Cocorico package.
// *
// * (c) Cocolabs SAS <contact@cocolabs.io>
// *
// * For the full copyright and license information, please view the LICENSE
// * file that was distributed with this source code.
// */
//
//namespace Cocorico\GeoBundle\Geocoder\Provider;
//
//use Exception;
//use Geocoder\Exception\InvalidCredentials;
//use Geocoder\Exception\NoResult;
//use Geocoder\Exception\QuotaExceeded;
//use Geocoder\Exception\UnsupportedOperation;
//use Ivory\HttpAdapter\HttpAdapterInterface;
//
//use Geocoder\Collection;
//use Geocoder\Exception\InvalidServerResponse;
//use Geocoder\Model\AddressCollection;
//use Geocoder\Model\AddressBuilder;
//use Geocoder\Query\GeocodeQuery;
//use Geocoder\Query\ReverseQuery;
//use Geocoder\Http\Provider\AbstractHttpProvider;
//use Geocoder\Provider\GoogleMaps\Model\GoogleAddress;
//use Geocoder\Provider\Provider;
//use Http\Client\HttpClient;
//
//class GoogleMapsProvider extends GoogleMaps
//{
//    /**
//     * @var bool
//     */
//    private $useSsl;
//
//    /**
//     * @var bool
//     */
//    const DEBUG = false;
//
//    public function __construct(HttpClient $client, string $region = null, string $apiKey = null)
//    {
//        parent::__construct($client, $region, $apiKey);
//    }
//
//
////    private function executeQuery($query)
////    {
////        $this->debug("executeQuery > query:\n" . $query);
////        $query = $this->buildQuery($query);
////        $this->debug("executeQuery > buildQuery > query :\n" . $query);
////
////        $content = (string)$this->getAdapter()->get($query)->getBody();
////        $this->debug("executeQuery > getAdapter > body :\n" . $content);
////
////        // Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
////        if (strpos($content, "Provided 'signature' is not valid for the provided client ID") !== false) {
////            throw new InvalidCredentials(sprintf('Invalid client ID / API Key %s', $query));
////        }
////
////        if (empty($content)) {
////            throw new NoResult(sprintf('Empty content > Could not execute query "%s".', $query));
////        }
////
////        $json = json_decode($content);
////
////        // API error
////        if (!isset($json)) {
////            throw new NoResult(sprintf('JSON not set > Could not execute query "%s".', $query));
////        }
////
////        if ('REQUEST_DENIED' === $json->status && 'The provided API key is invalid.' === $json->error_message) {
////            throw new InvalidCredentials(sprintf('API key is invalid %s', $query));
////        }
////
////        if ('REQUEST_DENIED' === $json->status) {
////            $this->debug("executeQuery > REQUEST_DENIED :\n");
////            throw new Exception(
////                sprintf(
////                    'API access denied. Request: %s - Message: %s',
////                    $query,
////                    $json->error_message
////                )
////            );
////        }
////
////        // you are over your quota
////        if ('OVER_QUERY_LIMIT' === $json->status) {
////            throw new QuotaExceeded(sprintf('Daily quota exceeded %s', $query));
////        }
////
////        // no result
////        if (!isset($json->results) || !count($json->results) || 'OK' !== $json->status) {
////            throw new NoResult(sprintf('No result > Could not execute query "%s".', $query));
////        }
////
////        $content = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $content);
////        $json = json_decode($content);
////
////        $results = array();
////
////        //This format is also used on client side (@see geocoding_js.html.twig).
////        //It must be the same on both sides
////        foreach ($json->results as $i => $result) {
////            $resultSet = array();
////            // update address components
////            foreach ($result->address_components as $component) {
////                foreach ($component->types as $type) {
////                    $resultSet[$this->getLocale()][$type] = $component->long_name;
////                    $resultSet[$this->getLocale()][$type . "_short"] = $component->short_name;
////                }
////            }
////
////            // update coordinates
////            $resultSet['formatted_address'] = $result->formatted_address;
////            $geometry = $result->geometry;
////            $resultSet['location_type'] = $geometry->location_type ? $geometry->location_type : "PLACES";
////            $viewport = $geometry->viewport;
////            $resultSet['viewport'] = $viewport;
////
////            $resultSet['bounds'] = null;
////            if (isset($geometry->bounds)) {
////                $resultSet['bounds'] = $geometry->bounds;
////            }
////            $location = $geometry->location;
////            $resultSet['location'] = $location;
////            $resultSet['lat'] = $location->lat;
////            $resultSet['lng'] = $location->lng;
////
////            $results = $resultSet;
//////            echo "<pre>";
//////            print_r($resultSet);
//////            echo "</pre>";
////
////            //Sometimes google change the order of its results for the same request and  send as first result
////            //a place with less informations than the next result.
////            //So in this case we the following result.
////            //Same checking is done in GeocodingToCoordinateEntityTransformer->getGeocoding($geocodingI18n)
////            if (
////                !isset($results[$this->getLocale()]) ||
////                !isset($results[$this->getLocale()]["country_short"]) ||
////                !isset($results['formatted_address']) ||
////                (
////                    !isset($results[$this->getLocale()]["administrative_area_level_1"]) &&
////                    !isset($results[$this->getLocale()]["administrative_area_level_2"])
////                )
////            ) {
////                continue;
////            } else {
////                break; //Only first
////            }
////        }
////
////        return $results;
////    }
//
//
//    /**
//     * @param float $latitude
//     * @param float $longitude
//     * @return \Geocoder\Model\AddressCollection
//     */
//    public function reverse($latitude, $longitude)
//    {
//        return $this->geocode(sprintf('%F,%F', $latitude, $longitude));
//    }
//
//
//    /**
//     * {@inheritDoc}
//     */
//    public function geocode($address)
//    {
//        // Google API returns invalid data if IP address given
//        // This API doesn't handle IPs
//        if (filter_var($address, FILTER_VALIDATE_IP)) {
//            throw new UnsupportedOperation(
//                'The GoogleMaps provider does not support IP addresses, only street addresses.'
//            );
//        }
//
//        $query = sprintf(
//            $this->useSsl ? self::ENDPOINT_URL_SSL : self::ENDPOINT_URL,
//            rawurlencode($address)
//        );
//
//        return $this->executeQuery($query);
//    }
//
//
//    /**
//     * @param $message
//     */
//    private function debug($message)
//    {
//        if (self::DEBUG) {
//            echo nl2br($message) . "<br>";
//        }
//    }
//}

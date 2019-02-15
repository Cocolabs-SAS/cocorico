<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Cocorico\GeoBundle\Geocoder\Provider;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Http\Client\HttpClient;

/**
 * @author William Durand <william.durand1@gmail.com>
 * @author Cocolabs
 */
class GoogleMaps extends AbstractHttpProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/geocode/json?bbb&address=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%F,%F';

    /**
     * @var bool
     */
    const DEBUG = false;

    /**
     * @var string|null
     */
    private $region;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string|null
     */
    private $privateKey;


    /**
     * Google Maps for Business
     * https://developers.google.com/maps/documentation/business/.
     *
     * @param HttpClient $client     An HTTP adapter
     * @param string     $clientId   Your Client ID
     * @param string     $privateKey Your Private Key (optional)
     * @param string     $region     Region biasing (optional)
     * @param string     $apiKey     Google Geocoding API key (optional)
     *
     * @return GoogleMaps
     */
    public static function business(
        HttpClient $client,
        $clientId,
        $privateKey = null,
        $region = null,
        $apiKey = null
    ) {
        $provider = new self($client, $region, $apiKey);
        $provider->clientId = $clientId;
        $provider->privateKey = $privateKey;

        return $provider;
    }

    /**
     * @param HttpClient $client An HTTP adapter
     * @param string     $region Region biasing (optional)
     * @param string     $apiKey Google Geocoding API key (optional)
     */
    public function __construct(HttpClient $client, $region = null, $apiKey = null)
    {
        parent::__construct($client);

        $this->region = $region;
        $this->apiKey = $apiKey;
    }

    /** @inheritdoc */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // Google API returns invalid data if IP address given
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation(
                'The GoogleMaps provider does not support IP addresses, only street addresses.'
            );
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL_SSL, rawurlencode($query->getText()));
        if (null !== $bounds = $query->getBounds()) {
            $url .= sprintf(
                '&bounds=%s,%s|%s,%s',
                $bounds->getSouth(),
                $bounds->getWest(),
                $bounds->getNorth(),
                $bounds->getEast()
            );
        }

        return $this->fetchUrl($url, $query->getLocale(), $query->getLimit(), $query->getData('region', $this->region));
    }

    /** @inheritdoc */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinate = $query->getCoordinates();
        $url = sprintf(self::REVERSE_ENDPOINT_URL_SSL, $coordinate->getLatitude(), $coordinate->getLongitude());

        if (null !== $locationType = $query->getData('location_type')) {
            $url .= '&location_type=' . urlencode($locationType);
        }

        if (null !== $resultType = $query->getData('result_type')) {
            $url .= '&result_type=' . urlencode($resultType);
        }

        return $this->fetchUrl($url, $query->getLocale(), $query->getLimit(), $query->getData('region', $this->region));
    }

    /** @inheritdoc */
    public function reverseQueryAsJson(ReverseQuery $query)
    {
        $coordinate = $query->getCoordinates();
        $url = sprintf(self::REVERSE_ENDPOINT_URL_SSL, $coordinate->getLatitude(), $coordinate->getLongitude());

        if (null !== $locationType = $query->getData('location_type')) {
            $url .= '&location_type=' . urlencode($locationType);
        }

        if (null !== $resultType = $query->getData('result_type')) {
            $url .= '&result_type=' . urlencode($resultType);
        }

        return $this->fetchUrlAsJson(
            $url,
            $query->getLocale(),
            $query->getLimit(),
            $query->getData('region', $this->region)
        );
    }


    /**
     * @param string $url
     * @param string $locale
     * @param string $region
     *
     * @return string query with extra params
     */
    private function buildQuery(string $url, string $locale = null, string $region = null)
    {
        if (null !== $locale) {
            $url = sprintf('%s&language=%s', $url, $locale);
        }

        if (null !== $region) {
            $url = sprintf('%s&region=%s', $url, $region);
        }

        if (null !== $this->apiKey) {
            $url = sprintf('%s&key=%s', $url, $this->apiKey);
        }

        if (null !== $this->clientId) {
            $url = sprintf('%s&client=%s', $url, $this->clientId);

            if (null !== $this->privateKey) {
                $url = $this->signQuery($url);
            }
        }

        return $url;
    }

    /**
     * @param string      $url
     * @param string|null $locale
     * @param int         $limit
     * @param string|null $region
     * @return AddressCollection
     * @throws InvalidCredentials
     * @throws InvalidServerResponse
     * @throws QuotaExceeded
     */
    private function fetchUrl(string $url, string $locale = null, int $limit, string $region = null): AddressCollection
    {
        $this->debug("fetchUrl > url:\n" . $url);
        $url = $this->buildQuery($url, $locale, $region);
        $content = $this->getUrlContents($url);
        $this->debug("fetchUrl > content:\n" . $content);
        $json = $this->validateResponse($url, $content);

        // no result
        if (!isset($json->results) || !count($json->results) || 'OK' !== $json->status) {
            throw new InvalidServerResponse(sprintf('No result > Could not fetch url "%s".', $url));
        }

        $results = [];

        //This format is also used on client side (@see geocoding_js.html.twig).
        //It must be the same on both sides
        foreach ($json->results as $i => $result) {
            $resultSet = array();
            // update address components
            foreach ($result->address_components as $component) {
                foreach ($component->types as $type) {
                    $resultSet[$locale][$type] = $component->long_name;
                    $resultSet[$locale][$type . "_short"] = $component->short_name;
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
                !isset($results[$locale]) ||
                !isset($results[$locale]["country_short"]) ||
                !isset($results['formatted_address']) ||
                (
                    !isset($results[$locale]["administrative_area_level_1"]) &&
                    !isset($results[$locale]["administrative_area_level_2"])
                )
            ) {
                continue;
            } else {
                break; //Only first
            }
        }

        return new AddressCollection([0 => $results]);
    }

    /**
     * @param string      $url
     * @param string|null $locale
     * @param int         $limit
     * @param string|null $region
     * @return mixed result form json_decode()
     * @throws InvalidCredentials
     * @throws InvalidServerResponse
     * @throws QuotaExceeded
     */
    private function fetchUrlAsJson(string $url, string $locale = null, int $limit, string $region = null)
    {
        $this->debug("fetchUrlAsJson > url:\n" . $url);
        $url = $this->buildQuery($url, $locale, $region);

        $content = $this->getUrlContents($url);
        $this->debug("fetchUrlAsJson > content:\n" . $content);

        return $this->validateResponse($url, $content);
    }

    /**
     * Sign a URL with a given crypto key
     * Note that this URL must be properly URL-encoded
     * src: http://gmaps-samples.googlecode.com/svn/trunk/urlsigning/UrlSigner.php-source.
     *
     * @param string $query Query to be signed
     *
     * @return string $query query with signature appended
     */
    private function signQuery(string $query): string
    {
        $url = parse_url($query);

        $urlPartToSign = $url['path'] . '?' . $url['query'];

        // Decode the private key into its binary format
        $decodedKey = base64_decode(str_replace(['-', '_'], ['+', '/'], $this->privateKey));

        // Create a signature using the private key and the URL-encoded
        // string using HMAC SHA1. This signature will be binary.
        $signature = hash_hmac('sha1', $urlPartToSign, $decodedKey, true);

        $encodedSignature = str_replace(['+', '/'], ['-', '_'], base64_encode($signature));

        return sprintf('%s&signature=%s', $query, $encodedSignature);
    }

    /**
     * Decode the response content and validate it to make sure it does not have any errors.
     *
     * @param string $url
     * @param string $content
     *
     * @return mixed result form json_decode()
     *
     * @throws InvalidCredentials
     * @throws InvalidServerResponse
     * @throws QuotaExceeded
     */
    private function validateResponse(string $url, $content)
    {
        // Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
        if (strpos($content, "Provided 'signature' is not valid for the provided client ID") !== false) {
            throw new InvalidCredentials(sprintf('Invalid client ID / API Key %s', $url));
        }

        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw InvalidServerResponse::create($url);
        }

        if ('REQUEST_DENIED' === $json->status && 'The provided API key is invalid.' === $json->error_message) {
            throw new InvalidCredentials(sprintf('API key is invalid %s', $url));
        }

        if ('REQUEST_DENIED' === $json->status) {
            throw new InvalidServerResponse(
                sprintf('API access denied. Request: %s - Message: %s', $url, $json->error_message)
            );
        }

        // you are over your quota
        if ('OVER_QUERY_LIMIT' === $json->status) {
            throw new QuotaExceeded(sprintf('Daily quota exceeded %s', $url));
        }

        return $json;
    }


    /**
     * @param $message
     */
    private function debug($message)
    {
        if (self::DEBUG) {
            echo nl2br($message) . "<br>";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'google_maps';
    }

}

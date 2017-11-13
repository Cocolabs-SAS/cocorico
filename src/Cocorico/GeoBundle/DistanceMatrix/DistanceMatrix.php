<?php

/*
 * This file is part of the Cocorico package and Ivory Google Map package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io> for modified work
 * (c) Eric GELOEN <geloen.eric@gmail.com> for Ivory Google Map package
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\DistanceMatrix;


use GuzzleHttp\Client;

/**
 * DistanceMatrix service
 * Require "guzzlehttp/guzzle"
 *
 * @author CocolabsSAS
 * @author GeLo <geloen.eric@gmail.com>
 * @author Tyler Sommer <sommertm@gmail.com>
 */
class DistanceMatrix extends AbstractDistanceMatrixConstants
{
    /**
     * @var Client
     */
    private $client;


    /**
     * Creates a distance matrix service.
     *
     */
    public function __construct()
    {
        $this->client = new Client();
    }


    /**
     * Processes the given request.
     *
     * @param DistanceMatrixRequest $distanceMatrixRequest
     *
     * @return DistanceMatrixResponse
     * @throws \Exception
     */
    public function process(DistanceMatrixRequest $distanceMatrixRequest)
    {
        if (!$distanceMatrixRequest->isValid()) {
            throw new \Exception('Invalid Distance Matrix Request');
        }

        $response = $this->send($this->generateUrl($distanceMatrixRequest));
        $distanceMatrixResponse = $this->buildDistanceMatrixResponse($this->parse($response->getBody()));

        return $distanceMatrixResponse;
    }

    /**
     * Generates distance matrix URL API according to the request.
     *
     * @param DistanceMatrixRequest $distanceMatrixRequest The distance matrix request.
     *
     * @return string The generated URL.
     */
    protected function generateUrl(DistanceMatrixRequest $distanceMatrixRequest)
    {
        $httpQuery = array(
            'origins' => array(),
            'destinations' => array(),
        );

        foreach ($distanceMatrixRequest->getOrigins() as $origin) {
            $httpQuery['origins'][] = $origin;
        }

        foreach ($distanceMatrixRequest->getDestinations() as $destination) {
            $httpQuery['destinations'][] = $destination;
        }

        $httpQuery['origins'] = implode('|', $httpQuery['origins']);
        $httpQuery['destinations'] = implode('|', $httpQuery['destinations']);

        if ($distanceMatrixRequest->getTravelMode()) {
            $httpQuery['mode'] = strtolower($distanceMatrixRequest->getTravelMode());
        }

        if ($distanceMatrixRequest->getAvoidTolls()) {
            $httpQuery['avoidTolls'] = true;
        }

        if ($distanceMatrixRequest->getAvoidHighways()) {
            $httpQuery['avoidHighways'] = true;
        }

        if ($distanceMatrixRequest->getUnitSystem()) {
            $httpQuery['units'] = strtolower($distanceMatrixRequest->getUnitSystem());
        }

        if ($distanceMatrixRequest->getLanguage()) {
            $httpQuery['language'] = $distanceMatrixRequest->getLanguage();
        }

        $url = sprintf('%s/%s?%s', self::ENDPOINT_URL_SSL, 'json', http_build_query($httpQuery));

        return $url;
    }

    /**
     * Parses & normalizes the distance matrix API result response.
     *
     * @param string $response The distance matrix API response.
     *
     * @return \stdClass The parsed & normalized distance matrix response.
     */
    protected function parse($response)
    {
        return $this->parseJSON($response);
    }

    /**
     * Parses & normalizes a JSON distance matrix API result response.
     *
     * @param string $response The distance matrix API JSON response.
     *
     * @return \stdClass The parsed & normalized distance matrix response.
     */
    protected function parseJSON($response)
    {
        return json_decode($response);
    }


    /**
     * Builds the distance matrix response according to the normalized distance matrix API results.
     *
     * @param \stdClass $distanceMatrixResponse The normalized distance matrix response.
     *
     * @return DistanceMatrixResponse The built distance matrix response.
     */
    protected function buildDistanceMatrixResponse(\stdClass $distanceMatrixResponse)
    {
        $status = $distanceMatrixResponse->status;
        $destinations = $distanceMatrixResponse->destination_addresses;
        $origins = $distanceMatrixResponse->origin_addresses;
        $rows = $this->buildDistanceMatrixRows($distanceMatrixResponse->rows);

        return new DistanceMatrixResponse($status, $origins, $destinations, $rows);
    }

    /**
     * Builds the distance matrix response rows according to the normalized distance matrix API results.
     *
     * @param array $rows The normalized distance matrix response rows.
     *
     * @return array The built distance matrix response rows.
     */
    protected function buildDistanceMatrixRows($rows)
    {
        $results = array();

        foreach ($rows as $row) {
            $results[] = $this->buildDistanceMatrixRow($row);
        }

        return $results;
    }

    /**
     * Builds a distance matrix response row according to the normalized distance matrix API response row.
     *
     * @param \stdClass $row The normalized distance matrix response row.
     *
     * @return DistanceMatrixResponseRow The built distance matrix response row.
     */
    protected function buildDistanceMatrixRow($row)
    {
        $elements = array();

        foreach ($row->elements as $element) {
            $elements[] = $this->buildDistanceMatrixResponseElement($element);
        }

        return new DistanceMatrixResponseRow($elements);
    }

    /**
     * Builds a distance matrix response element according to the normalized distance matrix API response elements.
     *
     * @param \stdClass $element The normalized distance matrix response element.
     *
     * @return DistanceMatrixResponseElement The built distance matrix response element.
     */
    protected function buildDistanceMatrixResponseElement($element)
    {
        $status = $element->status;
        $distance = null;
        $duration = null;

        if ($element->status === self::STATUS_OK) {
            $distance = new Distance($element->distance->text, $element->distance->value);
            $duration = new Duration($element->duration->text, $element->duration->value);
        }

        return new DistanceMatrixResponseElement($status, $distance, $duration);
    }


    /**
     * Sends a service request.
     *
     * @param string $url The service url.
     *
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     *
     * @throws \Exception If the response is null or has an error 4XX or 5XX.
     */
    protected function send($url)
    {
        $response = $this->client->get($url);
        if ($response === null) {
            throw new \Exception('Invalid service response null');
        }
        $statusCode = (string)$response->getStatusCode();
        if ($statusCode[0] === '4' || $statusCode[0] === '5') {
            throw new \Exception('Invalid service response ' . $statusCode);
        }

        return $response;
    }
}
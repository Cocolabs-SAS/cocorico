<?php

declare(strict_types=1);


namespace Cocorico\GeoBundle\Geocoder\Provider;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Query\ReverseQuery;

/**
 * Providers MUST always be stateless and immutable.
 * Add reverseAsJson
 */
interface ProviderInterface extends \Geocoder\Provider\Provider
{
    /**
     * @param ReverseQuery $query
     * @return mixed result form json_decode()
     * @throws InvalidCredentials
     * @throws InvalidServerResponse
     * @throws QuotaExceeded
     */
    public function reverseQueryAsJson(ReverseQuery $query);

}

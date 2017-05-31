<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\DistanceMatrix;


abstract class AbstractDistanceMatrixConstants
{
    const ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/distancematrix';

    const TRAVEL_BICYCLING = 'BICYCLING';
    const TRAVEL_DRIVING = 'DRIVING';
    const TRAVEL_WALKING = 'WALKING';
    const TRAVEL_TRANSIT = 'TRANSIT';

    public static $travelModes = array(
        self::TRAVEL_BICYCLING,
        self::TRAVEL_DRIVING,
        self::TRAVEL_WALKING,
        self::TRAVEL_TRANSIT,
    );

    const UNIT_IMPERIAL = 'IMPERIAL';
    const UNIT_METRIC = 'METRIC';

    public static $unitSystems = array(
        self::UNIT_IMPERIAL,
        self::UNIT_METRIC,
    );

    const STATUS_INVALID_REQUEST = 'INVALID_REQUEST';
    const STATUS_MAX_DIMENSIONS_EXCEEDED = 'MAX_DIMENSIONS_EXCEEDED';
    const STATUS_MAX_ELEMENTS_EXCEEDED = 'MAX_ELEMENTS_EXCEEDED';
    const STATUS_OK = 'OK';
    const STATUS_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';
    const STATUS_REQUEST_DENIED = 'REQUEST_DENIED';
    const STATUS_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    public static $status = array(
        self::STATUS_INVALID_REQUEST,
        self::STATUS_MAX_DIMENSIONS_EXCEEDED,
        self::STATUS_MAX_ELEMENTS_EXCEEDED,
        self::STATUS_OK,
        self::STATUS_OVER_QUERY_LIMIT,
        self::STATUS_REQUEST_DENIED,
        self::STATUS_UNKNOWN_ERROR,
    );

    const STATUS_ELEMENT_NOT_FOUND = 'NOT_FOUND';
    const STATUS_ELEMENT_OK = 'OK';
    const STATUS_ELEMENT_ZERO_RESULTS = 'ZERO_RESULTS';

    public static $statusElements = array(
        self::STATUS_ELEMENT_NOT_FOUND,
        self::STATUS_ELEMENT_OK,
        self::STATUS_ELEMENT_ZERO_RESULTS,
    );
}
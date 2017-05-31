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


/**
 * DistanceMatrixRequest represents a google map distance matrix query.
 *
 * @author CocolabsSAS
 * @author GeLo <geloen.eric@gmail.com>
 * @author Tyler Sommer <sommertm@gmail.com>
 */

class DistanceMatrixRequest
{
    /** @var boolean */
    protected $avoidHighways;

    /** @var boolean */
    protected $avoidTolls;

    /** @var array */
    protected $destinations;

    /** @var array */
    protected $origins;

    /** @var string */
    protected $language;

    /** @var string */
    protected $travelMode;

    /** @var string */
    protected $unitSystem;


    /**
     * @param array $origins
     * @param array $destinations
     */
    public function __construct(array $origins, array $destinations)
    {
        $this->setOrigins($origins);
        $this->setDestinations($destinations);
    }

    /**
     * Checks if the distance matrix request avoid highways.
     *
     * @return boolean TRUE if the distance matrix request avoids highways else FALSE.
     */
    public function getAvoidHighways()
    {
        return $this->avoidHighways;
    }

    /**
     * Sets if the the distance matrix request avoids highways.
     *
     * @param boolean $avoidHighways TRUE if the distance matrix request avoids highways else FALSE.
     *
     */
    public function setAvoidHighways($avoidHighways = null)
    {
        $this->avoidHighways = $avoidHighways;
    }


    /**
     * Checks if the distance matrix request avoid tolls.
     *
     * @return boolean TRUE if the distance matrix request avoids tolls else FALSE.
     */
    public function getAvoidTolls()
    {
        return $this->avoidTolls;
    }

    /**
     * Sets if the the distance matrix request avoids tolls.
     *
     * @param boolean $avoidTolls TRUE if the distance matrix request avoids tolls else FALSE.
     *
     */
    public function setAvoidTolls($avoidTolls = null)
    {
        $this->avoidTolls = $avoidTolls;
    }


    /**
     * Gets the distance matrix request destinations
     *
     * @return array The distance matrix request destination.
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * Sets the request destinations.
     *
     * @param array $destinations The distance matrix request destinations.
     */
    public function setDestinations(array $destinations = array())
    {
        $this->destinations = array();

        foreach ($destinations as $destination) {
            $this->addDestination($destination);
        }
    }

    /**
     * Adds a destination to the request.
     *
     * @param $destination
     */
    public function addDestination($destination)
    {
        $this->destinations[] = $destination;
    }


    /**
     * Gets the distance matrix request origin.
     *
     * @return array The distance matrix request origin.
     */
    public function getOrigins()
    {
        return $this->origins;
    }

    /**
     * Sets the request origins.
     *
     * @param array $origins The distance matrix request origins.
     */
    public function setOrigins(array $origins = array())
    {
        $this->origins = array();

        foreach ($origins as $origin) {
            $this->addOrigin($origin);
        }
    }

    /**
     * Adds an origin to the request.
     *
     * @param $origin
     */
    public function addOrigin($origin)
    {
        $this->origins[] = $origin;
    }


    /**
     * Gets the distance matrix request language.
     *
     * @return string The direction request language.
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the distance matrix request language.
     *
     * @param string $language The distance matrix request language.
     *
     */
    public function setLanguage($language = null)
    {
        $this->language = $language;
    }

    /**
     * Gets the distance matrix request travel mode.
     *
     * @return string The distance matrix request travel mode.
     */
    public function getTravelMode()
    {
        return $this->travelMode;
    }

    /**
     * Sets the distance matrix request travel mode.
     *
     * @param string $travelMode The distance matrix request travel mode.
     * @throws \Exception
     */
    public function setTravelMode($travelMode = null)
    {
        $travelModes = array_diff(DistanceMatrix::$travelModes, array(DistanceMatrix::TRAVEL_TRANSIT));

        if (!in_array($travelMode, $travelModes) && ($travelMode !== null)) {
            throw new \Exception('Invalid distance matrix request travelMode');
        }

        $this->travelMode = $travelMode;
    }

    /**
     * Gets the distance matrix request unit system.
     *
     * @return string The distance matrix request unit system.
     */
    public function getUnitSystem()
    {
        return $this->unitSystem;
    }

    /**
     * Sets  the distance matrix request unit system.
     *
     * @param string $unitSystem The distance matrix request unit system.
     *
     * @throws \Exception
     */
    public function setUnitSystem($unitSystem = null)
    {
        if (!in_array($unitSystem, DistanceMatrix::$unitSystems) && ($unitSystem !== null)) {
            throw new \Exception('Invalid distance matrix request UnitSystem');
        }

        $this->unitSystem = $unitSystem;
    }


    /**
     * Checks if the distance matrix request is valid.
     *
     * @return boolean TRUE if the distance matrix request is valid else FALSE.
     */
    public function isValid()
    {
        return count($this->destinations) && count($this->origins);
    }
}
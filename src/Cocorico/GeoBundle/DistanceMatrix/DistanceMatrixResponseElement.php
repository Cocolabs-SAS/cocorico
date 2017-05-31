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
 * A distance matrix response wraps the distance results & the response status.
 *
 * @author CocolabsSAS
 * @author GeLo <geloen.eric@gmail.com>
 * @author Tyler Sommer <sommertm@gmail.com>
 */
class DistanceMatrixResponseElement
{

    /** @var string */
    protected $status;

    /** @var null|Distance */
    protected $distance;

    /** @var null|Duration */
    protected $duration;

    /**
     * Create a distance matrix response element.
     *
     * @param Distance $distance The element distance.
     * @param Duration $duration The element duration.
     * @param string   $status   The element status.
     */
    public function __construct($status, Distance $distance = null, Duration $duration = null)
    {
        $this->setStatus($status);

        if ($distance !== null) {
            $this->setDistance($distance);
        }

        if ($duration !== null) {
            $this->setDuration($duration);
        }
    }

    /**
     * Gets the distance matrix response status.
     *
     * @return string The distance matrix response status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the distance matrix response status.
     *
     * @param string $status The distance matrix status.
     *
     * @throws \Exception If the status is not valid.
     */
    public function setStatus($status)
    {
        if (!in_array($status, DistanceMatrix::$statusElements)) {
            throw new \Exception('Invalid distance matrix response element status');
        }

        $this->status = $status;
    }

    /**
     * Gets the step distance.
     *
     * @return Distance The step distance.
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Sets the step distance.
     *
     * @param Distance $distance The step distance.
     */
    public function setDistance(Distance $distance)
    {
        $this->distance = $distance;
    }

    /**
     * Gets the step duration.
     *
     * @return Duration The step duration.
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Sets the step duration
     *
     * @param Duration $duration The step duration.
     */
    public function setDuration(Duration $duration)
    {
        $this->duration = $duration;
    }
}
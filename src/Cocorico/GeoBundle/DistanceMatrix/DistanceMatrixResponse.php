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
class DistanceMatrixResponse
{
    /** @var string */
    protected $status;

    /** @var array */
    protected $destinations;

    /** @var array */
    protected $origins;

    /** @var array */
    protected $rows;

    /**
     * Create a distance matrix response.
     *
     * @param string $status       The response status.
     * @param array  $origins      The normalized origins.
     * @param array  $destinations The normalized destinations.
     * @param array  $rows         The rows of data returned.
     */
    public function __construct($status, array $origins, array $destinations, array $rows)
    {
        $this->setStatus($status);
        $this->setOrigins($origins);
        $this->setDestinations($destinations);
        $this->setRows($rows);
    }

    /**
     * Gets the distance matrix routes.
     *
     * @return array The distance matrix origins.
     */
    public function getOrigins()
    {
        return $this->origins;
    }

    /**
     * Sets the distance matrix routes.
     *
     * @param array $origins The distance matrix origins.
     */
    public function setOrigins(array $origins)
    {
        $this->origins = array();

        foreach ($origins as $origin) {
            $this->addOrigin($origin);
        }
    }

    /**
     * Add a distance matrix origin.
     *
     * @param string $origin The origin to add.
     */
    public function addOrigin($origin)
    {
        $this->origins[] = $origin;
    }

    /**
     * Gets the distance matrix destinations.
     *
     * @return array The distance matrix destinations.
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * Sets the distance matrix destinations.
     *
     * @param array $destinations The distance matrix routes.
     */
    public function setDestinations(array $destinations)
    {
        $this->destinations = array();

        foreach ($destinations as $destination) {
            $this->addDestination($destination);
        }
    }

    /**
     * Add a distance matrix destination.
     *
     * @param string $destination The destination to add.
     */
    public function addDestination($destination)
    {
        $this->destinations[] = $destination;
    }

    /**
     * Gets the distance matrix routes.
     *
     * @return DistanceMatrixResponseRow[] The distance matrix rows.
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Sets the distance matrix routes.
     *
     * @param array $rows The distance matrix routes.
     */
    public function setRows(array $rows)
    {
        $this->rows = array();

        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    /**
     * Add a distance matrix route.
     *
     * @param DistanceMatrixResponseRow $row The row to add.
     */
    public function addRow(DistanceMatrixResponseRow $row)
    {
        $this->rows[] = $row;
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
        if (!in_array($status, DistanceMatrix::$status)) {
            throw new \Exception('Invalid response status');
        }

        $this->status = $status;
    }
}
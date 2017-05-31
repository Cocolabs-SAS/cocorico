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
 * Distance inspired
 *
 * @author CocolabsSAS
 * @author GeLo <geloen.eric@gmail.com>
 */
class Distance
{
    /** @var string */
    protected $text;

    /** @var double */
    protected $value;

    /**
     * Creates a distance.
     *
     * @param string $text  The distance as text.
     * @param double $value The distance in meters.
     */
    public function __construct($text, $value)
    {
        $this->setText($text);
        $this->setValue($value);
    }

    /**
     * Gets the string representation of the distance value.
     *
     * @return string The distance as text.
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the string representation of the distance value.
     *
     * @param string $text The distance as text.
     *
     * @throws \Exception If the text is not valid.
     */
    public function setText($text)
    {
        if (!is_string($text)) {
            throw new \Exception('Invalid distance text');
        }

        $this->text = $text;
    }

    /**
     * Gets the distance in meters.
     *
     * @return double The distance in meters.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the distance in meters.
     *
     * @param double $value The distance in meters.
     *
     * @throws \Exception If the distance is not valid.
     */
    public function setValue($value)
    {
        if (!is_numeric($value)) {
            throw new \Exception('Invalid distance value');
        }

        $this->value = $value;
    }
}
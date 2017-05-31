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
 * A duration which describes a google map duration.
 *
 * @author CocolabsSAS
 * @author GeLo <geloen.eric@gmail.com>
 */
class Duration
{
    /** @var string */
    protected $text;

    /** @var double */
    protected $value;

    /**
     * Creates a duration.
     *
     * @param string $text  The duration as text.
     * @param double $value The duration in minutes.
     */
    public function __construct($text, $value)
    {
        $this->setText($text);
        $this->setValue($value);
    }

    /**
     * Gets the string representation of the duration value.
     *
     * @return string The duration as text.
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the string representation of the duration value
     *
     * @param string $text The duration as text.
     *
     * @throws \Exception If the text is not valid.
     */
    public function setText($text)
    {
        if (!is_string($text)) {
            throw new \Exception('Invalid duration text');
        }

        $this->text = $text;
    }

    /**
     * Gets the duration in minutes
     *
     * @return double The duration in minutes.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the duration in minutes
     *
     * @param double $value The duration in minutes.
     *
     * @throws \Exception If the value is not valid.
     */
    public function setValue($value)
    {
        if (!is_numeric($value)) {
            throw new \Exception('Invalid duration value');
        }

        $this->value = $value;
    }
}
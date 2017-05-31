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
 * A distance matrix response wraps the distance results & the response status inspired.
 *
 * @author CocolabsSAS
 * @author GeLo <geloen.eric@gmail.com>
 * @author Tyler Sommer <sommertm@gmail.com>
 */
class DistanceMatrixResponseRow
{
    /** @var DistanceMatrixResponseElement[] */
    protected $elements;

    /**
     * Create a distance matrix response row.
     *
     * @param array $elements The row elements.
     */
    public function __construct(array $elements)
    {
        $this->setElements($elements);
    }

    /**
     * Gets the distance matrix row elements.
     *
     * @return DistanceMatrixResponseElement[] The row elements.
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Sets the distance matrix row elements.
     *
     * @param array $elements The row elements.
     */
    public function setElements(array $elements)
    {
        $this->elements = array();

        foreach ($elements as $element) {
            $this->addElement($element);
        }
    }

    /**
     * Add a distance matrix element.
     *
     * @param DistanceMatrixResponseElement $element The element to add.
     */
    public function addElement(DistanceMatrixResponseElement $element)
    {
        $this->elements[] = $element;
    }
}
<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\Model\Manager;

use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Coordinate;
use Cocorico\GeoBundle\Entity\Country;
use Cocorico\GeoBundle\Entity\Department;
use Cocorico\GeoBundle\Entity\Geocoding;
use Doctrine\ORM\EntityManager;

class GeocodingManager
{
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Create a new geocoding entity for a particular coordinate
     *
     * @param Coordinate $coordinate
     * @param  string    $type
     * @param  string    $geocoding
     */
    public function createGeocoding(Coordinate $coordinate, $type, $geocoding)
    {
        $geocoding = json_decode($geocoding, true);

        try {
            $vp = $geocoding["viewport"];
            $vp = '((' . $vp["south"] . ', ' . $vp["west"] . '), (' . $vp["north"] . ', ' . $vp["east"] . '))';

            $geocodingEntity = new Geocoding();

            $type = "get" . ucfirst($type);
            /** @var Country|Area|Department|City $place */
            $place = $coordinate->$type();
            if (!$place->getGeocoding()) {
                $geocodingEntity->setLat($geocoding["location"]["lat"]);
                $geocodingEntity->setLng($geocoding["location"]["lng"]);
                $geocodingEntity->setViewport($vp);
                $geocodingEntity->setAddressType(implode(',', $geocoding["types"]));

                $place->setGeocoding($geocodingEntity);
                $this->em->persist($place);
                $this->em->flush();
            }
        } catch (\Exception $e) {

        }
    }
}

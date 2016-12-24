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

use Cocorico\CoreBundle\Entity\Listing;
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
     * Create a new geocoding entity for a particular geographical place
     *
     * @param Listing $listing
     * @param  string $type
     * @param  string $geocoding
     */
    public function createGeocoding(Listing $listing, $type, $geocoding)
    {
        $geocoding = json_decode($geocoding, true);

        try {
            $vp = $geocoding["viewport"];
            $vp = '((' . $vp["south"] . ', ' . $vp["west"] . '), (' . $vp["north"] . ', ' . $vp["east"] . '))';

            $coordinate = $listing->getLocation()->getCoordinate();
            $geocodingEntity = new Geocoding();

            if ($type == 'country') {
                $country = $coordinate->getCountry();
                if (!$country->getGeocoding()) {
                    $geocodingEntity->setLat($geocoding["location"]["lat"]);
                    $geocodingEntity->setLng($geocoding["location"]["lng"]);
                    $geocodingEntity->setViewport($vp);
                    $geocodingEntity->setAddressType(implode(',', $geocoding["types"]));

                    $country->setGeocoding($geocodingEntity);
                    $this->em->persist($country);
                    $this->em->flush();
                }
            }

            if ($type == 'area') {
                $area = $coordinate->getArea();
                if (!$area->getGeocoding()) {
                    $geocodingEntity->setLat($geocoding["location"]["lat"]);
                    $geocodingEntity->setLng($geocoding["location"]["lng"]);
                    $geocodingEntity->setViewport($vp);
                    $geocodingEntity->setAddressType(implode(',', $geocoding["types"]));

                    $area->setGeocoding($geocodingEntity);
                    $this->em->persist($area);
                    $this->em->flush();
                }
            }

            if ($type == 'department') {
                $department = $coordinate->getDepartment();
                if (!$department->getGeocoding()) {
                    $geocodingEntity->setLat($geocoding["location"]["lat"]);
                    $geocodingEntity->setLng($geocoding["location"]["lng"]);
                    $geocodingEntity->setViewport($vp);
                    $geocodingEntity->setAddressType(implode(',', $geocoding["types"]));

                    $department->setGeocoding($geocodingEntity);
                    $this->em->persist($department);
                    $this->em->flush();
                }
            }

            if ($type == 'city') {
                $city = $coordinate->getCity();
                if (!$city->getGeocoding()) {
                    $geocodingEntity->setLat($geocoding["location"]["lat"]);
                    $geocodingEntity->setLng($geocoding["location"]["lng"]);
                    $geocodingEntity->setViewport($vp);
                    $geocodingEntity->setAddressType(implode(',', $geocoding["types"]));

                    $city->setGeocoding($geocodingEntity);
                    $this->em->persist($city);
                    $this->em->flush();
                }
            }

        } catch (\Exception $e) {

        }
    }
}

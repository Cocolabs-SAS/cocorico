<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\GeoBundle\Twig;


class GeoExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $googlePlaceAPIKey;
    protected $ipInfoDbAPIKey;


    /**
     * @param string $googlePlaceAPIKey
     * @param string $ipInfoDbAPIKey
     */
    public function __construct($googlePlaceAPIKey, $ipInfoDbAPIKey)
    {
        $this->googlePlaceAPIKey = $googlePlaceAPIKey;
        $this->ipInfoDbAPIKey = $ipInfoDbAPIKey;
    }


    public function getGlobals()
    {
        return array(
            'googlePlaceAPIKey' => $this->googlePlaceAPIKey,
            'ipInfoDbAPIKey' => $this->ipInfoDbAPIKey,
        );
    }

    public function getName()
    {
        return 'cocorico_geo_extension';
    }
}

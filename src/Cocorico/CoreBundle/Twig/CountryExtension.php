<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Twig;

use Cocorico\CoreBundle\Utils\PHP;
use Symfony\Component\Intl\Intl;

/**
 * CountryExtension will render the name of the country
 */
class CountryExtension extends \Twig_Extension
{

//    private $request;
//    private $locale;
//    public function onKernelRequest(GetResponseEvent $event) {
//        if ($event->getRequestType() === HttpKernel::MASTER_REQUEST) {
//            $this->request = $event->getRequest();
//            $this->locale = $this->request->getLocale();
//        }
//    }

    /** @inheritdoc */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('country_name', array($this, 'countryNameFilter'))
        );

    }

    /**
     * Return the name of country code
     *
     * @param        $countryCode
     * @param string $locale
     *
     * @return mixed
     */
    public function countryNameFilter($countryCode, $locale = "en")
    {
        return Intl::getRegionBundle()->getCountryName($countryCode, $locale);
    }


    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('countries_translated', array($this, 'getCountriesTranslated')),
            new \Twig_SimpleFunction('culture_code', array($this, 'getCultureCode')),
        );
    }

    /**
     * Get countries localized
     *
     * @param $locale
     * @return null|string
     */
    public function getCountriesTranslated($locale)
    {
        return Intl::getRegionBundle()->getCountryNames($locale);
    }

    /**
     * Get culture code  (ex : 'en_GB') from locale
     *
     * @param $locale
     * @return null|string
     */
    public function getCultureCode($locale)
    {
        return PHP::locale_get_culture($locale);
    }

    /** @inheritdoc */
    public function getName()
    {
        return 'country_extension';
    }
}
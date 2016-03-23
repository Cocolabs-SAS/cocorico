<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CountryType as BaseCountryType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CountryFilteredType extends BaseCountryType
{
    private $countries;
    private $favoriteCountries;

    /**
     * @param array|null $countries
     * @param array      $favoriteCountries
     */
    public function __construct($countries, $favoriteCountries)
    {
        //print_r($countries);
        $this->countries = $countries;
        $this->favoriteCountries = $favoriteCountries;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $countries = Intl::getRegionBundle()->getCountryNames();
        if ($this->countries) {//Filtering countries app parameters
            $countries = array_intersect_key(
                Intl::getRegionBundle()->getCountryNames(),
                array_flip($this->countries)
            );
        }

        $resolver->setDefaults(
            array(
                'choices' => $countries,
                //'data' => $this->favoriteCountries[0],
                'preferred_choices' => $this->favoriteCountries,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'country';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'country_filtered';
    }
}

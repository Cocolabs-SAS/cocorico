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

use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageFilteredType extends LanguageType
{
    private $locales;

    /**
     * @param array|null $locales
     */
    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $locales = Intl::getLanguageBundle()->getLanguageNames();

        if ($this->locales) {//Filtering languages app parameters
            $locales = array_intersect_key(
                $locales,
                array_flip($this->locales)
            );
        }

        $resolver->setDefaults(
            array(
                'choices' => array_flip($locales),
                'required' => true,
            )
        );
    }

    /**
     * Get lang to translate depending on current locale
     *
     * @param $locales
     * @param $locale
     * @return mixed
     */
    public static function getLocaleTo($locales, $locale)
    {
        if (($key = array_search($locale, $locales)) !== false) {
            unset($locales[$key]);
        }
        $localeTo = reset($locales);
        $localeTo = $localeTo ? $localeTo : $locale;

        return $localeTo;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return LanguageType::class;
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'language_filtered';
    }
}

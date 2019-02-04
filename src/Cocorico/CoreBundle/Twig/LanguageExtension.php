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

use Symfony\Component\Intl\Intl;

/**
 * LanguageExtension will render the name of the language depending upon the code used for languages
 */
class LanguageExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        return array();
    }

    /** @inheritdoc */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('language_name', array($this, 'languageNameFilter'))
        );
    }


    /**
     * languageNameFilter checks the available name of language, and returns the name
     *
     * @param        $code
     * @param string $locale
     *
     * @return mixed
     */
    public function languageNameFilter($code, $locale = "en")
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames($locale);

        return array_key_exists($code, $languages) ? $languages[$code] : $code;
    }


    /** @inheritdoc */
    public function getName()
    {
        return 'language_extension';
    }

}
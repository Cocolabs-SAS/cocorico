<?php

namespace Cocorico\TimeBundle\Twig;

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class IntlExtension
 *
 * Tmp solution to fix old ICU versions having trouble with some timezone like Moscow.
 * To remove when ICU will be updated on servers
 *
 */
class IntlExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        return array();
    }

    /** @inheritdoc */
    public function __construct()
    {
        if (!class_exists('IntlDateFormatter')) {
            throw new \RuntimeException('The intl extension is needed to use intl-based filters.');
        }
    }

    /**
     * Override Intl Twig localizeddate filter to fix some timezone issue due to ICU old version
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'localizeddate', array($this, 'localizedDateFilter'), array('needs_environment' => true)
            ),
        );
    }

    /**
     * For now usages in cocorico are :
     *      - localizeddate('short', 'none', 'fr', user_timezone)
     *      - localizeddate('none', 'short', 'fr', user_timezone)
     *      - localizeddate('short', 'short', 'fr', user_timezone)
     *
     * @param \Twig_Environment $env
     * @param                   $date
     * @param string            $dateFormat
     * @param string            $timeFormat
     * @param null              $locale
     * @param null              $timezone
     * @param null              $format
     * @return bool|string
     */
    public function localizedDateFilter(
        \Twig_Environment $env,
        $date,
        $dateFormat = 'medium',
        $timeFormat = 'medium',
        $locale = null,
        $timezone = null,
        $format = null
    ) {
        $date = twig_date_converter($env, $date, $timezone);

        $formatValues = array(
            'none' => \IntlDateFormatter::NONE,
            'short' => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long' => \IntlDateFormatter::LONG,
            'full' => \IntlDateFormatter::FULL,
        );

        //Intl/ICU formatting in a special format
        $formatter = \IntlDateFormatter::create(
            'fr',
            $formatValues['short'],
            $formatValues['short'],
            $date->getTimezone()->getName(),
            \IntlDateFormatter::GREGORIAN
        );
        $formattedDate = $formatter->format($date->getTimestamp());

        if ($formattedDate == $date->format('d/m/Y H:i')) {//No problem with ICU timezone
            $formatter = \IntlDateFormatter::create(
                $locale,
                $formatValues[$dateFormat],
                $formatValues[$timeFormat],
                $date->getTimezone()->getName(),
                \IntlDateFormatter::GREGORIAN,
                $format
            );

            $formattedDate = $formatter->format($date->getTimestamp());
        } else {
            $format = 'd/m/Y H:i';
            if ($dateFormat == 'short' && $timeFormat == 'none') {
                $format = 'd/m/Y';
            } elseif ($dateFormat == 'none' && $timeFormat == 'short') {
                $format = 'H:i';
            }

            if ($locale != 'fr') {
                $format = 'm/d/Y h:i A';
                if ($dateFormat == 'short' && $timeFormat == 'none') {
                    $format = 'm/d/Y';
                } elseif ($dateFormat == 'none' && $timeFormat == 'short') {
                    $format = 'h:i A';
                }
            }

            $formattedDate = $date->format($format);
        }

        return $formattedDate;
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'cocorico_intl';
    }
}
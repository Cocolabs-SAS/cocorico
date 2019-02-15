<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\TimeBundle\Twig;

use Cocorico\TimeBundle\Utils\PHP;

class TimeExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{

    /**
     * TimeExtension constructor.
     */
    public function __construct()
    {

    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('format_seconds', array($this, 'formatSecondsFilter')),
            new \Twig_SimpleFilter('timezone_name', array($this, 'timezoneName'))
        );
    }

    /**
     * Format time from seconds to unit
     *
     * @param int    $seconds
     * @param string $format
     *
     * @return string
     */
    public function formatSecondsFilter($seconds, $format = 'dhm')
    {
        $time = PHP::seconds_to_time($seconds);
        switch ($format) {
            case 'h':
                $result = ($time['d'] * 24) + $time['h'] . "h";
                break;
            default:
                $result = ($time['d'] * 24) + $time['h'] . "h " . $time['m'] . "m";
        }

        return $result;
    }


    /**
     * @param string $timezone ex Europe/Paris
     *
     * @return string
     */
    public function timezoneName($timezone)
    {
        $parts = explode('/', $timezone);
        if (count($parts) > 2) {
            $name = $parts[1] . ' - ' . $parts[2];
        } elseif (count($parts) > 1) {
            $name = $parts[1];
        } else {
            $name = $parts[0];
        }

        return str_replace('_', ' ', $name);
    }

    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        return array();
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName()
    {
        return 'time_extension';
    }
}

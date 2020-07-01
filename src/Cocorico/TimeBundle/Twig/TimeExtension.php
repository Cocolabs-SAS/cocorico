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
use Symfony\Component\Translation\TranslatorInterface;

class TimeExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $translator;
    protected $timeUnit;
    protected $timeUnitAllDay;
    protected $timeUnitIsDay;

    /**
     * TimeExtension constructor.
     * @param TranslatorInterface $translator
     * @param                     $timeUnit
     * @param                     $timeUnitAllDay
     */
    public function __construct(TranslatorInterface $translator, $timeUnit, $timeUnitAllDay)
    {
        $this->translator = $translator;
        $this->timeUnit = $timeUnit;
        $this->timeUnitAllDay = $timeUnitAllDay;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('add_time_unit_text', array($this, 'addTimeUnitTextFilter')),
            new \Twig_SimpleFilter('format_seconds', array($this, 'formatSecondsFilter')),
            new \Twig_SimpleFilter('timezone_name', array($this, 'timezoneName'))
        );
    }

    /**
     * Add unit time text to duration value
     *
     * @param int    $duration
     * @param string $locale
     * @return string
     */
    public function addTimeUnitTextFilter($duration, $locale = null)
    {
        if ($this->timeUnitIsDay) {
            if ($this->timeUnitAllDay) {
                return $this->translator->transChoice(
                    'time_unit_day',
                    $duration,
                    array('%count%' => $duration),
                    'cocorico',
                    $locale
                );
            } else {
                return $this->translator->transChoice(
                    'time_unit_night',
                    $duration,
                    array('%count%' => $duration),
                    'cocorico',
                    $locale
                );
            }
        } else {
            return $this->translator->transChoice(
                'time_unit_hour',
                $duration,
                array('%count%' => $duration),
                'cocorico',
                $locale
            );
        }
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

<?php

namespace Cocorico\TimeBundle\Utils;

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PHP
{

    /**
     * Convert number of seconds into hours, minutes and seconds
     * and return an array containing those values
     *
     * @param integer $input_seconds Number of seconds to parse
     * @return array
     */
    public static function seconds_to_time($input_seconds)
    {
        $seconds_in_a_minute = 60;
        $seconds_in_an_hour = 60 * $seconds_in_a_minute;
        $seconds_in_a_day = 24 * $seconds_in_an_hour;

        // extract days
        $days = floor($input_seconds / $seconds_in_a_day);

        // extract hours
        $hour_seconds = $input_seconds % $seconds_in_a_day;
        $hours = floor($hour_seconds / $seconds_in_an_hour);

        // extract minutes
        $minute_seconds = $hour_seconds % $seconds_in_an_hour;
        $minutes = floor($minute_seconds / $seconds_in_a_minute);

        // extract the remaining seconds
        $remaining_seconds = $minute_seconds % $seconds_in_a_minute;
        $seconds = ceil($remaining_seconds);

        // return the final array
        $result = array(
            'd' => (int)$days,
            'h' => (int)$hours,
            'm' => (int)$minutes,
            's' => (int)$seconds,
        );

        return $result;
    }


}
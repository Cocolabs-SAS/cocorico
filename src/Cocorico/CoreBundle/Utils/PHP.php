<?php
namespace Cocorico\CoreBundle\Utils;

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

    public static function ksort_recursive(&$array, $sortFlags = SORT_STRING)
    {
        if (!is_array($array)) {
            return false;
        }
        ksort($array, $sortFlags);
        foreach ($array as &$arr) {
            self::ksort_recursive($arr, $sortFlags);
        }

        return true;
    }
}
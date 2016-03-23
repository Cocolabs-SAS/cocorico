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

use Symfony\Component\Form\Extension\Core\Type\DateType;


class DateHiddenType extends DateType
{
    public function getName()
    {
        return 'date_hidden';
    }

    public function getParent()
    {
        return 'hidden';
    }
}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\TimeBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class DateHiddenType extends DateType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'date_hidden';
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}

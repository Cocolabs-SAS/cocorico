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

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class TimeHiddenType extends TimeType
{
    public function getName()
    {
        return 'time_hidden';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->addAllowedValues(
            array(
                'widget' => array(
                    'hidden'
                )
            )
        );

    }

    public function getParent()
    {
        return 'hidden';
    }
}

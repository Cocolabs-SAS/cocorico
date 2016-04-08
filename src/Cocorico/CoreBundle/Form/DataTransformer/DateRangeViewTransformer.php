<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\DataTransformer;

use Cocorico\CoreBundle\Model\DateRange;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DateRangeViewTransformer implements DataTransformerInterface
{
    protected $options = array();

    public function __construct(OptionsResolver $resolver, array $options = array())
    {
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'end_day_included' => true,
            )
        );

        $resolver->setAllowedValues('end_day_included', array(true, false));
    }

    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof DateRange) {
            throw new UnexpectedTypeException($value, 'Cocorico\CoreBundle\Model\DateRange');
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof DateRange) {
            throw new UnexpectedTypeException($value, 'Cocorico\CoreBundle\Model\DateRange');
        }

        if ($this->options['end_day_included'] && $value->end) {
            $value->end->setTime(23, 59, 59);
        }

        return $value;
    }
}

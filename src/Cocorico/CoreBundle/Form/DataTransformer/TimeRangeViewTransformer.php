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

use Cocorico\CoreBundle\Model\TimeRange;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TimeRangeViewTransformer implements DataTransformerInterface
{
    protected $options = array();

    public function __construct(OptionsResolver $resolver, array $options = array())
    {
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof TimeRange) {
            throw new UnexpectedTypeException($value, 'Cocorico\CoreBundle\Model\TimeRange');
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof TimeRange) {
            throw new UnexpectedTypeException($value, 'Cocorico\CoreBundle\Model\TimeRange');
        }

        return $value;
    }
}

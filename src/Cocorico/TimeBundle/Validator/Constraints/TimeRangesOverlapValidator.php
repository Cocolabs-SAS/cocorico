<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\TimeBundle\Validator\Constraints;

use Cocorico\TimeBundle\Model\TimeRange;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TimeRangesOverlapValidator extends ConstraintValidator
{

    /**
     * @param TimeRange[] $timeRanges
     * @param Constraint|TimeRangesOverlap $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($timeRanges, Constraint $constraint)
    {
        if (!is_array($timeRanges) && !$timeRanges instanceof \Countable) {
            throw new UnexpectedTypeException($timeRanges, 'array or \Countable');
        }

        $count = count($timeRanges);

        if (null !== $constraint->max && $count > $constraint->max) {
            $this->context->buildViolation($constraint::$messageMax)
                ->atPath("time_ranges")
                ->setParameter('{{ limit }}', $constraint->max)
                ->setInvalidValue($timeRanges)
                ->setPlural((int)$constraint->max)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();

            return;
        }

        if (null !== $constraint->min && $count < $constraint->min) {
            $this->context->buildViolation($constraint::$messageMin)
                ->atPath("time_ranges")
                ->setParameter('{{ limit }}', $constraint->min)
                ->setInvalidValue($timeRanges)
                ->setPlural((int)$constraint->min)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }

        /** @var  TimeRange[] $timeRanges */
        foreach ($timeRanges as $i => $timeRange) {
            if (!$timeRange) {
                $this->context->buildViolation('time_range.invalid.required')
                    ->atPath("time_ranges[$i]")
                    ->setTranslationDomain('cocorico')
                    ->addViolation();
            } elseif (!$timeRange->getStart() || !$timeRange->getEnd() ||
                $this->timeOverlap($timeRange->getStart(), $timeRange->getEnd(), $timeRanges, $i)
            ) {
                $this->context->buildViolation($constraint::$messageOverlap)
                    ->atPath("time_ranges[$i]")
                    ->setTranslationDomain('cocorico_listing')
                    ->addViolation();
                break;
            }
        }
    }


    /**
     * @param \DateTime   $startTime
     * @param             $endTime
     * @param TimeRange[] $times
     * @param int         $current
     * @return bool true if overlap
     */
    public function timeOverlap(\DateTime $startTime, \DateTime $endTime, array $times, $current)
    {
        foreach ($times as $i => $time) {
            if ($i != $current && ($startTime < $time->getEnd()) && ($endTime > $time->getStart())) {
                return true;
            }
        }

        return false;
    }

}

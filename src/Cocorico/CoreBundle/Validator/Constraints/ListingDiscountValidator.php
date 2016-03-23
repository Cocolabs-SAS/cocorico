<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ListingDiscountValidator extends ConstraintValidator
{
    private $minDiscount;
    private $maxDiscount;

    /**
     * @param int $minDiscount
     * @param int $maxDiscount
     */
    public function __construct($minDiscount, $maxDiscount)
    {
        $this->minDiscount = $minDiscount;
        $this->maxDiscount = $maxDiscount;
    }

    /**
     * @param mixed      $listingDiscount
     * @param Constraint $constraint
     */
    public function validate($listingDiscount, Constraint $constraint)
    {
        /** @var $listingDiscount \Cocorico\CoreBundle\Entity\ListingDiscount */
        /** @var $constraint \Cocorico\CoreBundle\Validator\Constraints\ListingDiscount */

        //Discount
        if ($listingDiscount->getDiscount() < $this->minDiscount) {
            $this->context->buildViolation($constraint::$messageMinDiscount)
                ->atPath("discount")
                ->setParameter('{{ min_discount }}', $this->minDiscount)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }

        if ($listingDiscount->getDiscount() > $this->maxDiscount) {
            $this->context->buildViolation($constraint::$messageMaxDiscount)
                ->atPath("discount")
                ->setParameter('{{ max_discount }}', $this->maxDiscount)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }
    }

}

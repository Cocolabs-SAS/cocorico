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

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ListingDiscount extends Constraint implements TranslationContainerInterface
{
    public static $messageMinDiscount = "listing_discount.discount.min {{ min_discount }}";
    public static $messageMaxDiscount = "listing_discount.discount.max {{ max_discount }}";

    public function validatedBy()
    {
        return 'listing_discount';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$messageMinDiscount, 'cocorico_listing');
        $messages[] = new Message(self::$messageMaxDiscount, 'cocorico_listing');

        return $messages;
    }
}

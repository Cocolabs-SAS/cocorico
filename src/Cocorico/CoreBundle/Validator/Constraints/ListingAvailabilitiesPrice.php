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


class ListingAvailabilitiesPrice extends Constraint implements TranslationContainerInterface
{
    public static $messageMinPrice = "listing_price.min {{ min_price }}";


    public function validatedBy()
    {
        return 'listing_availabilities_price';
    }


    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$messageMinPrice, 'cocorico_listing');

        return $messages;
    }
}

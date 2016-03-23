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
class Booking extends Constraint implements TranslationContainerInterface
{
    public static $messageUnavailable = 'booking.new.error.unavailable';
    public static $messageDurationInvalid = 'booking.new.error.duration_invalid';
    public static $messageAmountInvalid = 'booking.new.error.amount_invalid {{ min_price }}';

//    public static $messageSelfBooking = 'booking.new.self_booking.error';

    public function validatedBy()
    {
        return 'booking';
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
        $messages[] = new Message(self::$messageUnavailable, 'cocorico_booking');
        $messages[] = new Message(self::$messageDurationInvalid, 'cocorico_booking');
        $messages[] = new Message(self::$messageAmountInvalid, 'cocorico_booking');

//        $messages[] = new Message(self::$messageSelfBooking, 'cocorico_booking');

        return $messages;
    }
}

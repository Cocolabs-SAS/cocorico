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
use Symfony\Component\Validator\Exception\MissingOptionsException;


class TimeRangesOverlap extends Constraint implements TranslationContainerInterface
{
    public static $messageOverlap = "time_ranges.overlap";
    public static $messageMin = "time_ranges.min {{ limit }}";
    public static $messageMax = "time_ranges.max {{ limit }}";
    public $min;
    public $max;

    public function __construct($options = null)
    {
        if (null !== $options && !is_array($options)) {
            $options = array(
                'min' => $options,
                'max' => $options,
            );
        }
        parent::__construct($options);

        if (null === $this->min && null === $this->max) {
            throw new MissingOptionsException(
                sprintf('Either option "min" or "max" must be given for constraint %s', __CLASS__), array('min', 'max')
            );
        }
    }

    public function validatedBy()
    {
        return 'time_ranges_overlap';
    }


    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$messageOverlap, 'cocorico_listing');
        $messages[] = new Message(self::$messageMin, 'cocorico_listing');
        $messages[] = new Message(self::$messageMax, 'cocorico_listing');

        return $messages;
    }
}

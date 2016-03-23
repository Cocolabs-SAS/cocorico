<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class User extends Constraint implements TranslationContainerInterface
{
    public static $messageMaxImages = "user_images.max {{ max_images }}";
    public static $messageMinImages = "user_images.min {{ min_images }}";

    public function validatedBy()
    {
        return 'user';
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
        $messages[] = new Message(self::$messageMaxImages, 'cocorico_user');
        $messages[] = new Message(self::$messageMinImages, 'cocorico_user');

        return $messages;
    }
}

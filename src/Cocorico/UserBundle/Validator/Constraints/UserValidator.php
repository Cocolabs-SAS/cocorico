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

use Cocorico\UserBundle\Entity\User as UserEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserValidator extends ConstraintValidator
{

    private $maxImages;
    private $minImages;

    /**
     * @param $maxImages
     * @param $minImages
     */
    public function __construct($maxImages, $minImages)
    {
        $this->maxImages = $maxImages;
        $this->minImages = $minImages;
    }

    /**
     * @param UserEntity $user
     * @param Constraint $constraint
     */
    public function validate($user, Constraint $constraint)
    {
        /** @var UserEntity $user */
        /** @var \Cocorico\UserBundle\Validator\Constraints\User $constraint */

        //Images
        if ($user->getImages()->count() > $this->maxImages) {
            $this->context->buildViolation($constraint::$messageMaxImages)
                ->atPath('image[new]')
                ->setParameter('{{ max_images }}', $this->maxImages)
                ->setTranslationDomain('cocorico_validators')
                ->addViolation();
        }

//        if ($user->getImages()->count() < $this->minImages) {
//            $this->context->buildViolation($constraint->messageMinImages)
//                ->atPath('images_new')
//                ->setParameter('{{ min_images }}', $this->minImages)
//                ->setTranslationDomain('cocorico_validators')
//                ->addViolation();
//        }

    }

}

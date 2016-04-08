<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\ReviewBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * Star Rating type for the rating, extended from the choices
 *
 */
class StarRatingType extends AbstractType
{

    /**
     * getParent returns the parent type which will be overriding
     *
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }


    /**
     * BC
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'star_rating';
    }
}
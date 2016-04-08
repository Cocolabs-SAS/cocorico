<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\ReviewBundle\Extension;

/**
 * StarRatingTwigExtension will render the star ratings in the twig,
 * using single line, depending upon the values for rating
 */
class StarRatingTwigExtension extends \Twig_Extension
{
    /**
     * [getFilters returns the filter method for the twig]
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'cocorico_star_rating', array($this, 'starRatingFilter'),
                array('needs_environment' => true, 'is_safe' => array('html'))
            ),
        );
    }

    /**
     * getName returns the name of the filter to render twig extension
     *
     * @return string
     */
    public function getName()
    {
        return 'cocorico_star_rating';
    }

    /**
     * startRatingFilter outputs the readonly starts
     *
     * @param \Twig_Environment $env
     * @param                   $rating
     * @return string
     */
    public function starRatingFilter(\Twig_Environment $env, $rating)
    {
        return $env->render('CocoricoReviewBundle:Frontend/Twig:star_rating.html.twig', array('rating' => $rating));
    }
}
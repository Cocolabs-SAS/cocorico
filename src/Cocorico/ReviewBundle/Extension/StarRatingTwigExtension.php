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

    /** @var \Twig_Environment $environment */
    private $environment = null;

    /**
     * [initRuntime override the initRuntime to access Twig environment variable]
     *
     * @param \Twig_Environment $environment
     *
     * @return void
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * [getFilters returns the filter method for the twig]
     *
     * @return Array
     */
    public function getFilters()
    {
        $filters = array(
            'cocorico_star_rating' => new \Twig_Filter_Method(
                $this,
                'starRatingFilter', array(
                    'is_safe' => array('html')
                )
            ),
        );

        return $filters;
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
     * Output read-only stars
     *
     * @param $rating
     * @return string
     */
    public function starRatingFilter($rating)
    {
        $argument = array('rating' => $rating);

        return $this->environment->render('CocoricoReviewBundle:Frontend/Twig:star_rating.html.twig', $argument);
    }
}
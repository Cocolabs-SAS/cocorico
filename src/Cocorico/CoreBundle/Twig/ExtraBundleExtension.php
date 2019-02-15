<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Twig;

/**
 * ExtraBundleExtension check if bundle exist
 */
class ExtraBundleExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $bundles;

    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        return array();
    }

    /**
     * @param array $bundles
     */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'bundleExist',
                array($this, 'bundleExist')
            ),
        );
    }

    /**
     * @param string $bundle
     * @return bool
     */
    public function bundleExist($bundle)
    {
        return array_key_exists($bundle, $this->bundles);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'extra_bundle_extension';
    }
}
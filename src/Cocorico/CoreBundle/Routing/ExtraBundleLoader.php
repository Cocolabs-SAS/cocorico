<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Routing;

use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class ExtraBundleLoader extends Loader
{
    protected $bundles;
    protected $env;

    public function __construct(array $bundles, $env)
    {
        $this->bundles = $bundles;
        $this->env = $env;
    }

    /**
     * Add routing from extra bundles
     *
     * @param mixed $resource
     * @param null  $type
     * @return RouteCollection
     * @throws FileLoaderLoadException
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        try {
            if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
                $resource = '@CocoricoMangoPayBundle/Resources/config/routing.yml';
                $type = 'yaml';
                $importedRoutes = $this->import($resource, $type);
                $collection->addCollection($importedRoutes);
            }

            if (array_key_exists("CocoricoMangoPayCardSavingBundle", $this->bundles)) {
                $resource = '@CocoricoMangoPayCardSavingBundle/Resources/config/routing.yml';
                $type = 'yaml';
                $importedRoutes = $this->import($resource, $type);
                $collection->addCollection($importedRoutes);
            }

            if (array_key_exists("CocoricoListingAlertBundle", $this->bundles)) {
                $resource = '@CocoricoListingAlertBundle/Resources/config/routing.yml';
                $type = 'yaml';
                $importedRoutes = $this->import($resource, $type);
                $collection->addCollection($importedRoutes);
            }

            if (array_key_exists("CocoricoListingOptionBundle", $this->bundles)) {
                $resource = '@CocoricoListingOptionBundle/Resources/config/routing.yml';
                $type = 'yaml';
                $importedRoutes = $this->import($resource, $type);
                $collection->addCollection($importedRoutes);
            }

            if (array_key_exists("CocoricoSMSBundle", $this->bundles) && $this->env == 'dev') {
                $resource = '@CocoricoSMSBundle/Resources/config/routing_dev.yml';
                $type = 'yaml';
                $importedRoutes = $this->import($resource, $type);
                $collection->addCollection($importedRoutes);
            }

            if (array_key_exists("CocoricoListingCategoryFieldBundle", $this->bundles)) {
                $resource = '@CocoricoListingCategoryFieldBundle/Resources/config/routing.yml';
                $type = 'yaml';
                $importedRoutes = $this->import($resource, $type);
                $collection->addCollection($importedRoutes);
            }

            if (array_key_exists("CocoricoListingDepositBundle", $this->bundles)) {
                $resource = '@CocoricoListingDepositBundle/Resources/config/routing.yml';
                $type = 'yaml';
                $importedRoutes = $this->import($resource, $type);
                $collection->addCollection($importedRoutes);
            }

        } catch (FileLoaderLoadException  $e) {
            throw new FileLoaderLoadException($resource);
        }

        return $collection;
    }


    public function supports($resource, $type = null)
    {
        return 'extra_bundle' === $type;
    }
}
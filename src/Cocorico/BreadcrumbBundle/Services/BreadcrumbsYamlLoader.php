<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BreadcrumbBundle\Services;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;

class BreadcrumbsYamlLoader extends FileLoader
{
    /**
     * @var YamlParser
     */
    protected $yamlParser;

    /**
     * Constructor.
     *
     * @param YamlParser           $yamlParser YamlParser instance
     * @param FileLocatorInterface $locator    FileLocator instance
     */
    public function __construct(YamlParser $yamlParser, FileLocatorInterface $locator)
    {
        $this->yamlParser = $yamlParser;

        parent::__construct($locator);
    }

    public function load($resource, $type = null)
    {
        $filePath = $this->locator->locate($resource);

        return $this->yamlParser->parse(file_get_contents($filePath));
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}

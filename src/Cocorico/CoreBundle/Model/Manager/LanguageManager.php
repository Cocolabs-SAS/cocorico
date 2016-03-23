<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;

class LanguageManager
{
    protected $em;
    protected $router;
    protected $locales;
    protected $languagesLinks = [];
    protected $slugs = [];

    /**
     * @param EntityManager   $em
     * @param RouterInterface $router
     * @param string[]        $locales locales Parameters
     */
    public function __construct(
        EntityManager $em,
        RouterInterface $router,
        $locales
    ) {
        $this->em = $em;
        $this->router = $router;
        $this->locales = $locales;
    }


    /**
     * getLanguageLinks returns the generated links depending upon the parameters provided.
     *
     * @param string $routeName   passes the current route name
     * @param array  $routeParams passes all route parameters
     * @param string $queryString passes the query string from the current route
     *
     * @return array $languagesLinks array of all language links depending upon the requests
     */
    public function getLanguageLinks($routeName, $routeParams, $queryString)
    {
        //Get slug translations to generate correct listing_show url for each languages
        if ($routeName == 'cocorico_listing_show') {
            $this->setTranslatedSlugs('CocoricoCoreBundle:Listing', $routeParams);
        }

        //Get slug translations to generate correct page_show url for each languages
        if ($routeName == 'cocorico_page_show') {
            $this->setTranslatedSlugs('CocoricoPageBundle:Page', $routeParams);
        }

        // generate the urls as per the locales available
        foreach ($this->locales as $locale) {
            if (isset($this->slugs[$locale])) {
                $routeParams["slug"] = $this->slugs[$locale];
            }

            $this->languagesLinks[$locale] = $this->router->generate(
                $routeName,
                array_merge(
                    $routeParams,
                    $queryString,
                    array("_locale" => $locale)
                )
            );
        }

        return $this->languagesLinks;
    }

    /**
     * setTranslatedSlugs sets translated slugs for the specific route name
     *
     * @param string $entityName  Entity name used to call repository function
     * @param array  $routeParams passes all route parameters
     *
     * @return void
     */
    private function setTranslatedSlugs($entityName, $routeParams)
    {
        /** @var mixed $entityTranslations */
        $entityTranslations = $this->em->getRepository($entityName)
            ->findTranslationsBySlug($routeParams['slug'], $routeParams['_locale']);

        foreach ($entityTranslations as $entityTranslation) {
            $this->slugs[$entityTranslation->getLocale()] = $entityTranslation->getSlug();
        }
    }
}

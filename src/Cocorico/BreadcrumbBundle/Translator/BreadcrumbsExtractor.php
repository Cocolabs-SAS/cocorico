<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BreadcrumbBundle\Translator;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;

class BreadcrumbsExtractor implements ExtractorInterface
{
    private $breadcrumbsLoader;
    private $domain = 'cocorico_breadcrumbs';

    public function __construct(LoaderInterface $breadcrumbsLoader, $translation_domain)
    {
        $this->breadcrumbsLoader = $breadcrumbsLoader;
        $this->domain = $translation_domain['translation_domain'];
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function extract()
    {
        $catalogue = new MessageCatalogue();

        $breadcrumbRoutes = $this->breadcrumbsLoader->load('breadcrumbs.yml');

        foreach ($breadcrumbRoutes as $key => $breadcrumbs) {
            foreach ($breadcrumbs as $breadcrumb) {
                if (!is_array($breadcrumb['text'])) {
                    $message = new Message($breadcrumb['text'], $this->domain);
                    $message->setDesc($breadcrumb['text']);
                    $catalogue->add($message);
                }
            }
        }

        return $catalogue;
    }
}

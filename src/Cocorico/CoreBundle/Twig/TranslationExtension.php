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


use Symfony\Bridge\Twig\Extension\TranslationExtension as BaseTranslationExtension;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TranslationExtension
 *
 * Override trans filter by adding suffix to translations to know if a text has been translated through web interface
 *
 * @package Cocorico\CoreBundle\Twig
 */
class TranslationExtension extends BaseTranslationExtension
{
    private $checkTranslation;

    const TRANS_SUFFIX = '☮';

    public function __construct(
        TranslatorInterface $translator,
        \Twig_NodeVisitorInterface $translationNodeVisitor = null
    ) {
        parent::__construct($translator, $translationNodeVisitor);
    }

    public function setCheckTranslation($checkTranslation)
    {
        $this->checkTranslation = $checkTranslation;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        if ($this->checkTranslation) {
            return array(
                new \Twig_SimpleFilter('trans', array($this, 'transOverride')),
                new \Twig_SimpleFilter('transchoice', array($this, 'transchoiceOverride')),
            );
        }

        return parent::getFilters();
    }

    public function transOverride($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getTranslator()->getLocale();
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        if ('messages' !== $domain && false === $this->translationExists($id, $domain, $locale)) {
            $domain = 'messages';
        }

        return $this->getTranslator()->trans($id, $parameters, $domain, $locale) . self::TRANS_SUFFIX;
    }

    public function transchoiceOverride($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getTranslator()->getLocale();
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        if ('messages' !== $domain && false === $this->translationExists($id, $domain, $locale)) {
            $domain = 'messages';
        }

        return $this->getTranslator()->transChoice(
            $id,
            $number,
            array_merge(array('%count%' => $number), $parameters),
            $domain,
            $locale
        ) . self::TRANS_SUFFIX;
    }

    protected function translationExists($id, $domain, $locale)
    {
        return $this->getTranslator()->getCatalogue($locale)->has((string)$id, $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translator';
    }
}
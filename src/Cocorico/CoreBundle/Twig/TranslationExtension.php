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

use Symfony\Bridge\Twig\NodeVisitor\TranslationDefaultDomainNodeVisitor;
use Symfony\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;
use Symfony\Bridge\Twig\TokenParser\TransChoiceTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TwigFilter;

/**
 * Class TranslationExtension
 *
 * Override trans filter by adding suffix to translations to know if a text has been translated through web interface
 *
 * @package Cocorico\CoreBundle\Twig
 *
 * @author  Fabien Potencier <fabien@symfony.com>
 * @author  Cocolabs SAS <contact@cocolabs.io>
 */
class TranslationExtension extends AbstractExtension
{
    private $translator;
    private $translationNodeVisitor;
    private $suffix = '';

    const TRANS_SUFFIX = '☮';

    public function __construct(
        TranslatorInterface $translator = null,
        NodeVisitorInterface $translationNodeVisitor = null
    ) {
        $this->translator = $translator;
        $this->translationNodeVisitor = $translationNodeVisitor;
    }

    public function setCheckTranslation($checkTranslation)
    {
        if ($checkTranslation) {
            $this->suffix = self::TRANS_SUFFIX;
        }
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('trans', array($this, 'trans')),
            new TwigFilter('transchoice', array($this, 'transchoice')),
        );
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return AbstractTokenParser[]
     */
    public function getTokenParsers()
    {
        return array(
            // {% trans %}Symfony is great!{% endtrans %}
            new TransTokenParser(),

            // {% transchoice count %}
            //     {0} There is no apples|{1} There is one apple|]1,Inf] There is {{ count }} apples
            // {% endtranschoice %}
            new TransChoiceTokenParser(),

            // {% trans_default_domain "foobar" %}
            new TransDefaultDomainTokenParser(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return array($this->getTranslationNodeVisitor(), new TranslationDefaultDomainNodeVisitor());
    }

    public function getTranslationNodeVisitor()
    {
        return $this->translationNodeVisitor ?: $this->translationNodeVisitor = new TranslationNodeVisitor();
    }

    public function trans($message, array $arguments = array(), $domain = null, $locale = null)
    {
        if (null === $this->translator) {
            return strtr($message, $arguments);
        }

        return $this->translator->trans(
                $message,
                $arguments,
                $domain,
                $locale
            ) . $this->suffix;
    }

    public function transchoice($message, $count, array $arguments = array(), $domain = null, $locale = null)
    {
        if (null === $this->translator) {
            return strtr($message, $arguments);
        }

        return $this->translator->transChoice(
                $message,
                $count,
                array_merge(array('%count%' => $count), $arguments),
                $domain,
                $locale
            ) . $this->suffix;
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translator';
    }
}

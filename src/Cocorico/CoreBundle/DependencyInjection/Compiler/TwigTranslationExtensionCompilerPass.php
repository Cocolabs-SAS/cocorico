<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\DependencyInjection\Compiler;

use Cocorico\CoreBundle\Twig\TranslationExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigTranslationExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        /**
         * Override Twig Translation extension
         */
        if ($container->getParameter('cocorico.check_translation') === true) {
            if ($container->hasDefinition('twig.extension.trans')) {
                $definition = $container->getDefinition('twig.extension.trans');
                $definition->setClass(TranslationExtension::class);
                $definition->addMethodCall(
                    'setCheckTranslation',
                    array($container->getParameter('cocorico.check_translation'))
                );
            }
        }
    }
}

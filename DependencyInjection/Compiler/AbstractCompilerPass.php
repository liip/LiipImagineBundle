<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @param string           $message
     * @param mixed[]          $replacements
     */
    protected function log(ContainerBuilder $container, $message, array $replacements = [])
    {
        if (count($replacements) > 0) {
            $message = vsprintf($message, $replacements);
        }

        if (SymfonyFramework::hasDirectContainerBuilderLogging()) {
            $container->log($this, $message);
        } else {
            $compiler = $container->getCompiler();
            $compiler->addLogMessage($compiler->getLoggingFormatter()->format($this, $message));
        }
    }
}

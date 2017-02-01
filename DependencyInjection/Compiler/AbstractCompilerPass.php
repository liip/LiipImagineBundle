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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    protected function log(ContainerBuilder $container, $message, array $replacements = array())
    {
        if (count($replacements) > 0) {
            $message = vsprintf($message, $replacements);
        }

        if (method_exists($container, 'log')) {
            $container->log($this, $message);
        } else {
            $compiler = $container->getCompiler();
            $formatter = $compiler->getLoggingFormatter();
            $compiler->addLogMessage($formatter->format($this, $message));
        }
    }
}

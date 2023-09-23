<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Loader;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

abstract class AbstractLoaderFactory implements LoaderFactoryInterface
{
    protected static string $namePrefix = 'liip_imagine.binary.loader';

    final protected function getChildLoaderDefinition(string $name = null): ChildDefinition
    {
        return new ChildDefinition(sprintf('%s.prototype.%s', static::$namePrefix, $name ?: $this->getName()));
    }

    final protected function setTaggedLoaderDefinition(string $name, Definition $definition, ContainerBuilder $container): string
    {
        $definition->addTag(static::$namePrefix, [
            'loader' => $name,
        ]);

        $definition->setPublic(true);

        $container->setDefinition(
            $id = sprintf('%s.%s', static::$namePrefix, $name),
            $definition
        );

        return $id;
    }
}

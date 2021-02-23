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
    /**
     * @var string
     */
    protected static $namePrefix = 'liip_imagine.binary.loader';

    /**
     * @param string|null $name
     *
     * @return ChildDefinition
     */
    final protected function getChildLoaderDefinition($name = null)
    {
        return new ChildDefinition(sprintf('%s.prototype.%s', static::$namePrefix, $name ?: $this->getName()));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    final protected function setTaggedLoaderDefinition($name, Definition $definition, ContainerBuilder $container)
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

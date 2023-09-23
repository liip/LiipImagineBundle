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

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemLoaderFactory extends AbstractLoaderFactory
{
    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $definition = $this->getChildLoaderDefinition($this->getChildLoaderName());

        $definition->replaceArgument(1, new Reference($config['filesystem_service']));

        return $this->setTaggedLoaderDefinition($name, $definition, $container);
    }

    public function getName(): string
    {
        return 'flysystem';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('filesystem_service')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    private function getChildLoaderName(): ?string
    {
        if (interface_exists(FilesystemOperator::class)) {
            return 'flysystem2';
        }

        return null;
    }
}

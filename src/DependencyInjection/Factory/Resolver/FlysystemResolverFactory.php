<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemResolverFactory extends AbstractResolverFactory
{
    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $resolverDefinition = $this->getChildResolverDefinition($this->getChildResolverName());
        $resolverDefinition->replaceArgument(0, new Reference($config['filesystem_service']));
        $resolverDefinition->replaceArgument(2, $config['root_url']);
        $resolverDefinition->replaceArgument(3, $config['cache_prefix']);
        $resolverDefinition->replaceArgument(4, $config['visibility']);
        $resolverDefinition->addTag('liip_imagine.cache.resolver', [
            'resolver' => $name,
        ]);

        $resolverId = 'liip_imagine.cache.resolver.'.$name;
        $container->setDefinition($resolverId, $resolverDefinition);

        return $resolverId;
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
                ->scalarNode('cache_prefix')
                    ->defaultValue('')
                ->end()
                ->scalarNode('root_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('visibility')
                    ->values(['public', 'private', 'noPredefinedVisibility'])
                    ->defaultValue('public')
                ->end()
            ->end();
    }

    private function getChildResolverName(): ?string
    {
        if (interface_exists(FilesystemOperator::class)) {
            return 'flysystem2';
        }

        return null;
    }
}

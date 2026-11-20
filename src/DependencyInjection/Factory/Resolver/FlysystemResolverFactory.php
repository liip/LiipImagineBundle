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
    private const CACHE_ARGUMENT_INDEX = 0;
    private const CACHE_CONFIG_KEY = 'cache';
    private const CACHED_RESOLVER_SUFFIX = '.cached';
    private const PSR_CACHE_RESOLVER_NAME = 'psr_cache';
    private const RESOLVER_ARGUMENT_INDEX = 1;
    private const RESOLVER_TAG = 'liip_imagine.cache.resolver';
    private const RESOLVER_TAG_KEY = 'resolver';

    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $resolverDefinition = $this->getChildResolverDefinition($this->getChildResolverName());
        $resolverDefinition->replaceArgument(0, new Reference($config['filesystem_service']));
        $resolverDefinition->replaceArgument(2, $config['root_url']);
        $resolverDefinition->replaceArgument(3, $config['cache_prefix']);
        $resolverDefinition->replaceArgument(4, $config['visibility']);
        $resolverId = static::$namePrefix.'.'.$name;
        $container->setDefinition($resolverId, $resolverDefinition);

        if ($config[self::CACHE_CONFIG_KEY]) {
            $cachedResolverId = $resolverId.self::CACHED_RESOLVER_SUFFIX;

            $container->setDefinition($cachedResolverId, $resolverDefinition);

            $cacheResolverDefinition = $this->getChildResolverDefinition(self::PSR_CACHE_RESOLVER_NAME);
            $cacheResolverDefinition->replaceArgument(self::CACHE_ARGUMENT_INDEX, new Reference($config[self::CACHE_CONFIG_KEY]));
            $cacheResolverDefinition->replaceArgument(self::RESOLVER_ARGUMENT_INDEX, new Reference($cachedResolverId));

            $container->setDefinition($resolverId, $cacheResolverDefinition);
        }

        $container->getDefinition($resolverId)->addTag(self::RESOLVER_TAG, [
            self::RESOLVER_TAG_KEY => $name,
        ]);

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
                ->scalarNode(self::CACHE_CONFIG_KEY)
                    ->defaultFalse()
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

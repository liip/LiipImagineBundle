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

use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractWebPathResolverFactory extends AbstractResolverFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $resolverDefinition = $this->getChildResolverDefinition();
        $pathResolverDefinition = new ChildDefinition('liip_imagine.util.resolver.prototype.path');
        $pathResolverDefinition->replaceArgument(0, $config['web_root']);
        $pathResolverDefinition->replaceArgument(1, $config['cache_prefix']);

        $pathResolverServiceId = 'liip_imagine.util.resolver.path';
        $container->setDefinition($pathResolverServiceId, $pathResolverDefinition);

        $resolverDefinition->replaceArgument(1, new Reference($pathResolverServiceId));

        $resolverDefinition->addTag(
            'liip_imagine.cache.resolver',
            [
                'resolver' => $resolverName,
            ]
        );

        $resolverId = 'liip_imagine.cache.resolver.';
        $container->setDefinition($resolverId.$resolverName, $resolverDefinition);

        return $resolverId;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('web_root')
                    ->defaultValue(SymfonyFramework::getContainerResolvableRootWebPath())
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cache_prefix')
                    ->defaultValue('media/cache')
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }
}

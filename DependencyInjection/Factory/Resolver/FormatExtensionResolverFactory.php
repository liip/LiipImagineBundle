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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates another configured resolver with the FormatExtensionResolver, so that cached images
 * get the file extension of the format of their filter set.
 */
class FormatExtensionResolverFactory extends AbstractResolverFactory
{
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $resolverDefinition = $this->getChildResolverDefinition();
        $resolverDefinition->replaceArgument(0, new Reference('liip_imagine.cache.resolver.'.$config['resolver']));
        $resolverDefinition->addTag('liip_imagine.cache.resolver', [
            'resolver' => $resolverName,
        ]);

        $resolverId = 'liip_imagine.cache.resolver.'.$resolverName;
        $container->setDefinition($resolverId, $resolverDefinition);

        return $resolverId;
    }

    public function getName()
    {
        return 'format_extension';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('resolver')
                    ->info('Name of the configured resolver to decorate, for example "default".')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }
}

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
    /**
     * @param array<string, mixed> $config
     */
    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $resolverDefinition = $this->getChildResolverDefinition();
        $resolverDefinition->replaceArgument(0, new Reference('liip_imagine.cache.resolver.'.$config['resolver']));
        $resolverDefinition->addTag('liip_imagine.cache.resolver', [
            'resolver' => $name,
        ]);

        $resolverId = 'liip_imagine.cache.resolver.'.$name;
        $container->setDefinition($resolverId, $resolverDefinition);

        return $resolverId;
    }

    public function getName(): string
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

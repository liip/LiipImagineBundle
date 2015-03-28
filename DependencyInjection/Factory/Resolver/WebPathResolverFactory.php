<?php

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class WebPathResolverFactory implements ResolverFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $resolverDefinition = new DefinitionDecorator('liip_imagine.cache.resolver.prototype.web_path');
        $resolverDefinition->replaceArgument(2, $config['web_root']);
        $resolverDefinition->replaceArgument(3, $config['cache_prefix']);
        $resolverDefinition->addTag('liip_imagine.cache.resolver', array(
            'resolver' => $resolverName,
        ));
        $resolverId = 'liip_imagine.cache.resolver.'.$resolverName;

        $container->setDefinition($resolverId, $resolverDefinition);

        return $resolverId;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'web_path';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('web_root')->defaultValue('%kernel.root_dir%/../web')->cannotBeEmpty()->end()
                ->scalarNode('cache_prefix')->defaultValue('media/cache')->cannotBeEmpty()->end()
            ->end()
        ;
    }
}

<?php

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class AwsS3SdkV3ResolverFactory extends AwsS3ResolverFactoryBase
{
    /**
     * Add client config appropriate for AWS Sdk v3.
     *
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function addAwsClientConfig()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('client_config');
        $node
            ->isRequired()
            ->children()
                ->arrayNode('credentials')
                    ->isRequired()
                    ->children()
                        ->scalarNode('key')->end()
                        ->scalarNode('secret')->end()
                        ->scalarNode('token')->end()
                    ->end()
                ->end()
                ->scalarNode('api_provider')->end()
                ->scalarNode('debug')->end()
                ->scalarNode('endpoint')->end()
                ->scalarNode('endpoint_provider')->end()
                ->scalarNode('handler')->end()
                ->scalarNode('http')->end()
                ->scalarNode('http_handler')->end()
                ->scalarNode('profile')->end()
                ->scalarNode('region')->isRequired()->end()
                ->scalarNode('retries')->end()
                ->scalarNode('scheme')->end()
                ->scalarNode('signature_provider')->end()
                ->scalarNode('signature_version')->end()
                ->scalarNode('validate')->end()
                ->scalarNode('version')->isRequired()->end()
            ->end();

        return $node;
    }
}

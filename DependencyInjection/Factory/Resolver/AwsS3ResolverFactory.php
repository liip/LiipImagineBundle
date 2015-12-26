<?php

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class AwsS3ResolverFactory extends AwsS3ResolverFactoryBase
{
    /**
     * Add client config legacy AWS Sdk v2 style.
     *
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function addAwsClientConfig()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('client_config');
        $node
            ->isRequired()
            ->useAttributeAsKey('key')
            ->prototype('scalar')->end()
            ->end();

        return $node;
    }
}

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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AwsS3ResolverFactory extends AbstractResolverFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $awsS3ClientId = 'liip_imagine.cache.resolver.'.$resolverName.'.client';
        $awsS3ClientDefinition = new Definition('Aws\S3\S3Client');
        $awsS3ClientDefinition->setFactory(['Aws\S3\S3Client', 'factory']);
        $awsS3ClientDefinition->addArgument($config['client_config']);
        $container->setDefinition($awsS3ClientId, $awsS3ClientDefinition);

        $resolverDefinition = $this->getChildResolverDefinition();
        $resolverDefinition->replaceArgument(0, new Reference($awsS3ClientId));
        $resolverDefinition->replaceArgument(1, $config['bucket']);
        $resolverDefinition->replaceArgument(2, $config['acl']);
        $resolverDefinition->replaceArgument(3, $config['get_options']);
        $resolverDefinition->replaceArgument(4, $config['put_options']);

        $resolverId = 'liip_imagine.cache.resolver.'.$resolverName;
        $container->setDefinition($resolverId, $resolverDefinition);

        if (isset($config['cache_prefix'])) {
            $resolverDefinition->addMethodCall('setCachePrefix', [$config['cache_prefix']]);
        }

        if ($config['proxies']) {
            $proxiedResolverId = 'liip_imagine.cache.resolver.'.$resolverName.'.proxied';

            $container->setDefinition($proxiedResolverId, $resolverDefinition);

            $proxyResolverDefinition = $this->getChildResolverDefinition('proxy');
            $proxyResolverDefinition->replaceArgument(0, new Reference($proxiedResolverId));
            $proxyResolverDefinition->replaceArgument(1, $config['proxies']);

            $container->setDefinition($resolverId, $proxyResolverDefinition);
        }

        if ($config['cache']) {
            $cachedResolverId = 'liip_imagine.cache.resolver.'.$resolverName.'.cached';

            $container->setDefinition($cachedResolverId, $container->getDefinition($resolverId));

            $cacheResolverDefinition = $this->getChildResolverDefinition('cache');
            $cacheResolverDefinition->replaceArgument(0, new Reference($config['cache']));
            $cacheResolverDefinition->replaceArgument(1, new Reference($cachedResolverId));

            $container->setDefinition($resolverId, $cacheResolverDefinition);
        }

        $container->getDefinition($resolverId)->addTag('liip_imagine.cache.resolver', [
            'resolver' => $resolverName,
        ]);

        return $resolverId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'aws_s3';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('bucket')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cache')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('acl')
                    ->defaultValue('public-read')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cache_prefix')
                    ->defaultValue(null)
                ->end()
                ->arrayNode('client_config')
                    ->isRequired()
                    ->prototype('variable')
                        ->treatNullLike([])
                    ->end()
                ->end()
                ->arrayNode('get_options')
                    ->useAttributeAsKey('key')
                        ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('put_options')
                    ->useAttributeAsKey('key')
                        ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('proxies')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                        ->prototype('scalar')
                    ->end()
                ->end()
            ->end();
    }
}

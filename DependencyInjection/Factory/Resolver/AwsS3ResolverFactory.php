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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AwsS3ResolverFactory extends AbstractResolverFactory
{
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $awsS3ClientId = 'liip_imagine.cache.resolver.'.$resolverName.'.client';

        if ($config['client_id']) {
            $container->setAlias($awsS3ClientId, new Alias($config['client_id']));
        } else {
            $container->setDefinition($awsS3ClientId, (new Definition('Aws\S3\S3Client'))
                ->setFactory(['Aws\S3\S3Client', 'factory'])
                ->addArgument($config['client_config'])
            );
        }

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

            if (false === $config['use_psr_cache']) {
                trigger_deprecation('liip/imagine-bundle', '2.13.4', \sprintf('Setting the "liip_imagine.resolvers.%s.%s.use_psr_cache" config option to "false" is deprecated.', $resolverName, $this->getName()));
            }

            $cacheResolverDefinition = $this->getChildResolverDefinition($config['use_psr_cache'] ? 'psr_cache' : 'cache');
            $cacheResolverDefinition->replaceArgument(0, new Reference($config['cache']));
            $cacheResolverDefinition->replaceArgument(1, new Reference($cachedResolverId));

            $container->setDefinition($resolverId, $cacheResolverDefinition);
        }

        $container->getDefinition($resolverId)->addTag('liip_imagine.cache.resolver', [
            'resolver' => $resolverName,
        ]);

        return $resolverId;
    }

    public function getName()
    {
        return 'aws_s3';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('bucket')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cache')
                    ->defaultFalse()
                ->end()
                ->booleanNode('use_psr_cache')
                    ->defaultFalse()
                ->end()
                ->scalarNode('acl')
                    ->defaultValue('public-read')
                ->end()
                ->scalarNode('cache_prefix')
                    ->defaultValue('')
                ->end()
                ->scalarNode('client_id')
                    ->defaultNull()
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
            ->end()
            ->beforeNormalization()
                ->ifTrue(static function ($v) {
                    return isset($v['client_id']) && isset($v['client_config']);
                })
                ->then(static function ($v) {
                    throw new InvalidConfigurationException('Children config "client_id" and "client_config" cannot be configured at the same time.');
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(static function ($v) {
                    return isset($v['client_id']);
                })
                ->then(function ($config) {
                    $config['client_config'] = [];

                    return $config;
                })
            ->end();
    }
}

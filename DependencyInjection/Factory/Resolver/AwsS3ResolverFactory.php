<?php

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class AwsS3ResolverFactory implements ResolverFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $awsS3ClientId = 'liip_imagine.cache.resolver.'.$resolverName.'.client';
        $awsS3ClientDefinition = new Definition('Aws\S3\S3Client');
        if (method_exists($awsS3ClientDefinition, 'setFactory')) {
            $awsS3ClientDefinition->setFactory(array('Aws\S3\S3Client', 'factory'));
        } else {
            // to be removed when dependency on Symfony DependencyInjection is bumped to 2.6
            $awsS3ClientDefinition->setFactoryClass('Aws\S3\S3Client');
            $awsS3ClientDefinition->setFactoryMethod('factory');
        }
        $awsS3ClientDefinition->addArgument($config['client_config']);
        $container->setDefinition($awsS3ClientId, $awsS3ClientDefinition);

        $resolverDefinition = new DefinitionDecorator('liip_imagine.cache.resolver.prototype.aws_s3');
        $resolverDefinition->replaceArgument(0, new Reference($awsS3ClientId));
        $resolverDefinition->replaceArgument(1, $config['bucket']);
        $resolverDefinition->replaceArgument(2, $config['acl']);
        $resolverDefinition->replaceArgument(3, array_replace($config['url_options'], $config['get_options']));
        $resolverDefinition->replaceArgument(4, $config['put_options']);
        $resolverId = 'liip_imagine.cache.resolver.'.$resolverName;
        $container->setDefinition($resolverId, $resolverDefinition);

        if (isset($config['cache_prefix'])) {
            $resolverDefinition->addMethodCall('setCachePrefix', array($config['cache_prefix']));
        }

        if ($config['proxies']) {
            $proxiedResolverId = 'liip_imagine.cache.resolver.'.$resolverName.'.proxied';

            $container->setDefinition($proxiedResolverId, $resolverDefinition);

            $proxyResolverDefinition = new DefinitionDecorator('liip_imagine.cache.resolver.prototype.proxy');
            $proxyResolverDefinition->replaceArgument(0, new Reference($proxiedResolverId));
            $proxyResolverDefinition->replaceArgument(1, $config['proxies']);

            $container->setDefinition($resolverId, $proxyResolverDefinition);
        }

        if ($config['cache']) {
            $cachedResolverId = 'liip_imagine.cache.resolver.'.$resolverName.'.cached';

            $container->setDefinition($cachedResolverId, $container->getDefinition($resolverId));

            $cacheResolverDefinition = new DefinitionDecorator('liip_imagine.cache.resolver.prototype.cache');
            $cacheResolverDefinition->replaceArgument(0, new Reference($config['cache']));
            $cacheResolverDefinition->replaceArgument(1, new Reference($cachedResolverId));

            $container->setDefinition($resolverId, $cacheResolverDefinition);
        }

        $container->getDefinition($resolverId)->addTag('liip_imagine.cache.resolver', array(
            'resolver' => $resolverName,
        ));

        return $resolverId;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'aws_s3';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('bucket')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('cache')->defaultValue(false)->end()
                ->scalarNode('acl')->defaultValue('public-read')->cannotBeEmpty()->end()
                ->scalarNode('cache_prefix')->defaultValue(null)->end()
                ->arrayNode('client_config')
                    ->isRequired()
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                /* @deprecated Use `get_options` instead */
                ->arrayNode('url_options')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('get_options')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('put_options')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('proxies')
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }
}

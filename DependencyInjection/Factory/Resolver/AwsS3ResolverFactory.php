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
        $awsS3ClientDefinition =  new Definition('Aws\S3\S3Client');
        $awsS3ClientDefinition->setFactoryClass('Aws\S3\S3Client');
        $awsS3ClientDefinition->setFactoryMethod('factory');
        $awsS3ClientDefinition->addArgument($config['client_config']);
        $awsS3ClientId = 'liip_imagine.cache.resolver.'.$resolverName.'.client';
        $container->setDefinition($awsS3ClientId, $awsS3ClientDefinition);

        $resolverDefinition = new DefinitionDecorator('liip_imagine.cache.resolver.prototype.aws_s3');
        $resolverDefinition->replaceArgument(0, new Reference($awsS3ClientId));
        $resolverDefinition->replaceArgument(1, $config['bucket']);
        $resolverDefinition->replaceArgument(2, $config['acl']);
        $resolverDefinition->replaceArgument(3, $config['url_options']);
        $resolverId = 'liip_imagine.cache.resolver.'.$resolverName;
        $container->setDefinition($resolverId, $resolverDefinition);

        if ($config['cache']) {
            $internalResolverId = 'liip_imagine.cache.resolver.'.$resolverName.'.internal';

            $container->setDefinition($internalResolverId, $resolverDefinition);

            $cacheResolverDefinition = new DefinitionDecorator('liip_imagine.cache.resolver.prototype.cache');
            $cacheResolverDefinition->replaceArgument(0, new Reference($config['cache']));
            $cacheResolverDefinition->replaceArgument(1, new Reference($internalResolverId));

            $container->setDefinition($resolverId, $cacheResolverDefinition);
        }

        $container->getDefinition($resolverId)->addTag('liip_imagine.cache.resolver', array(
            'resolver' => $resolverName
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
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('bucket')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('cache')->defaultValue(false)->end()
                ->scalarNode('acl')->defaultValue('public-read')->cannotBeEmpty()->end()
                ->arrayNode('client_config')
                    ->isRequired()
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('url_options')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }
}

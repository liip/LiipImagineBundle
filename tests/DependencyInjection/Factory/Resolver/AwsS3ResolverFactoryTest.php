<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Resolver;

use Aws\S3\S3Client;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory<extended>
 */
class AwsS3ResolverFactoryTest extends AbstractTest
{
    public function testImplementsResolverFactoryInterface(): void
    {
        $rc = new \ReflectionClass(AwsS3ResolverFactory::class);

        $this->assertTrue($rc->implementsInterface(ResolverFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments(): void
    {
        $loader = new AwsS3ResolverFactory();

        $this->assertInstanceOf(AwsS3ResolverFactory::class, $loader);
    }

    public function testReturnExpectedName(): void
    {
        $resolver = new AwsS3ResolverFactory();

        $this->assertSame('aws_s3', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'client_config' => [],
            'bucket' => 'theBucket',
            'acl' => 'theAcl',
            'get_options' => ['fooKey' => 'fooVal'],
            'put_options' => ['barKey' => 'barVal'],
            'cache' => false,
            'proxies' => [],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.aws_s3', $resolverDefinition->getParent());

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(0));
        $this->assertSame('liip_imagine.cache.resolver.the_resolver_name.client', (string) $resolverDefinition->getArgument(0));

        $this->assertSame('theBucket', $resolverDefinition->getArgument(1));
        $this->assertSame('theAcl', $resolverDefinition->getArgument(2));
        $this->assertSame(['fooKey' => 'fooVal'], $resolverDefinition->getArgument(3));
        $this->assertSame(['barKey' => 'barVal'], $resolverDefinition->getArgument(4));
    }

    public function testCreateS3ClientDefinitionOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'client_config' => ['theClientConfigKey' => 'theClientConfigVal'],
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'get_options' => [],
            'put_options' => [],
            'cache' => false,
            'proxies' => [],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name.client'));

        $clientDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name.client');
        $this->assertSame(S3Client::class, $clientDefinition->getClass());
        $this->assertSame(['theClientConfigKey' => 'theClientConfigVal'], $clientDefinition->getArgument(0));
    }

    public function testCreateS3ClientDefinitionWithFactoryOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'client_config' => ['theClientConfigKey' => 'theClientConfigVal'],
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'get_options' => [],
            'put_options' => [],
            'cache' => false,
            'proxies' => [],
        ]);

        $clientDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name.client');
        $this->assertSame([S3Client::class, 'factory'], $clientDefinition->getFactory());
    }

    public function testWrapResolverWithProxyOnCreateWithoutCache(): void
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'client_config' => [],
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'get_options' => [],
            'put_options' => [],
            'cache' => false,
            'proxies' => ['foo'],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name.proxied');
        $this->assertInstanceOf(ChildDefinition::class, $proxiedResolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertFalse($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name.cached'));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.proxy', $resolverDefinition->getParent());

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(0));
        $this->assertSame('liip_imagine.cache.resolver.the_resolver_name.proxied', (string) $resolverDefinition->getArgument(0));

        $this->assertSame(['foo'], $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithCacheOnCreateWithoutProxy(): void
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'client_config' => [],
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'get_options' => [],
            'put_options' => [],
            'cache' => 'the_cache_service_id',
            'proxies' => [],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name.cached'));
        $cachedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name.cached');
        $this->assertInstanceOf(ChildDefinition::class, $cachedResolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.aws_s3', $cachedResolverDefinition->getParent());

        $this->assertFalse($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name.proxied'));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.cache', $resolverDefinition->getParent());

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(0));
        $this->assertSame('the_cache_service_id', (string) $resolverDefinition->getArgument(0));

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(1));
        $this->assertSame('liip_imagine.cache.resolver.the_resolver_name.cached', (string) $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithProxyAndCacheOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'client_config' => [],
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'get_options' => [],
            'put_options' => [],
            'cache' => 'the_cache_service_id',
            'proxies' => ['foo'],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name.proxied');
        $this->assertInstanceOf(ChildDefinition::class, $proxiedResolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name.cached'));
        $cachedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name.cached');
        $this->assertInstanceOf(ChildDefinition::class, $cachedResolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.proxy', $cachedResolverDefinition->getParent());

        $this->assertInstanceOf(Reference::class, $cachedResolverDefinition->getArgument(0));
        $this->assertSame('liip_imagine.cache.resolver.the_resolver_name.proxied', (string) $cachedResolverDefinition->getArgument(0));

        $this->assertSame(['foo'], $cachedResolverDefinition->getArgument(1));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.cache', $resolverDefinition->getParent());

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(0));
        $this->assertSame('the_cache_service_id', (string) $resolverDefinition->getArgument(0));

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(1));
        $this->assertSame('liip_imagine.cache.resolver.the_resolver_name.cached', (string) $resolverDefinition->getArgument(1));
    }

    public function testSetCachePrefixIfDefined(): void
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'client_config' => [],
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'get_options' => [],
            'put_options' => [],
            'cache_prefix' => 'theCachePrefix',
            'cache' => null,
            'proxies' => [],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');

        $methodCalls = $resolverDefinition->getMethodCalls();

        $this->assertCount(1, $methodCalls);
        $this->assertSame('setCachePrefix', $methodCalls[0][0]);
        $this->assertSame(['theCachePrefix'], $methodCalls[0][1]);
    }

    public function testThrowBucketNotSetOnAddConfiguration(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessageMatchesBC('/^The child (node|config) "bucket" (at path|under) "aws_s3" must be configured\.$/');

        $treeBuilder = new TreeBuilder('aws_s3');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('aws_s3');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, []);
    }

    public function testThrowClientConfigNotSetOnAddConfiguration(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessageMatchesBC('/^The child (node|config) "client_config" (at path|under) "aws_s3" must be configured\.$/');

        $treeBuilder = new TreeBuilder('aws_s3');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('aws_s3');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket' => 'aBucket',
            ],
        ]);
    }

    public function testThrowClientConfigNotArrayOnAddConfiguration(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessageMatchesBC('{^Invalid type for path "aws_s3.client_config". Expected (\")?array(\")?, but got (\")?string(\")?$}');

        $treeBuilder = new TreeBuilder('aws_s3');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('aws_s3');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket' => 'aBucket',
                'client_config' => 'not_array',
            ],
        ]);
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration(): void
    {
        $expectedClientConfig = [
            'theKey' => 'theClientConfigVal',
            'theOtherKey' => 'theOtherClientConfigValue',
        ];
        $expectedGetOptions = [
            'theKey' => 'theGetOptionsVal',
            'theOtherKey' => 'theOtherGetOptionsValue',
        ];
        $expectedObjectOptions = [
            'theKey' => 'theObjectOptionsVal',
            'theOtherKey' => 'theOtherObjectOptionsValue',
        ];
        $expectedBucket = 'theBucket';
        $expectedAcl = 'theAcl';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder('aws_s3');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('aws_s3');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket' => $expectedBucket,
                'acl' => $expectedAcl,
                'client_config' => $expectedClientConfig,
                'get_options' => $expectedGetOptions,
                'put_options' => $expectedObjectOptions,
                'cache_prefix' => $expectedCachePrefix,
            ],
        ]);

        $this->assertArrayHasKey('bucket', $config);
        $this->assertSame($expectedBucket, $config['bucket']);

        $this->assertArrayHasKey('acl', $config);
        $this->assertSame($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('client_config', $config);
        $this->assertSame($expectedClientConfig, $config['client_config']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertSame($expectedGetOptions, $config['get_options']);

        $this->assertArrayHasKey('put_options', $config);
        $this->assertSame($expectedObjectOptions, $config['put_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertSame($expectedCachePrefix, $config['cache_prefix']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration(): void
    {
        $expectedAcl = 'public-read';

        $treeBuilder = new TreeBuilder('aws_s3');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('aws_s3');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket' => 'aBucket',
                'client_config' => [],
            ],
        ]);

        $this->assertArrayHasKey('acl', $config);
        $this->assertSame($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertSame([], $config['get_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertNull($config['cache_prefix']);
    }

    public function testSupportAwsV3ClientConfig(): void
    {
        $expectedClientConfig = [
            'credentials' => [
                'key' => 'theKey',
                'secret' => 'theSecret',
                'token' => 'theToken',
            ],
            'region' => 'theRegion',
            'version' => 'theVersion',
        ];
        $expectedGetOptions = [
            'theKey' => 'theGetOptionsVal',
            'theOtherKey' => 'theOtherGetOptionsValue',
        ];
        $expectedObjectOptions = [
            'theKey' => 'theObjectOptionsVal',
            'theOtherKey' => 'theOtherObjectOptionsValue',
        ];
        $expectedBucket = 'theBucket';
        $expectedAcl = 'theAcl';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder('aws_s3');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('aws_s3');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket' => $expectedBucket,
                'acl' => $expectedAcl,
                'client_config' => $expectedClientConfig,
                'get_options' => $expectedGetOptions,
                'put_options' => $expectedObjectOptions,
                'cache_prefix' => $expectedCachePrefix,
            ],
        ]);

        $this->assertArrayHasKey('bucket', $config);
        $this->assertSame($expectedBucket, $config['bucket']);

        $this->assertArrayHasKey('acl', $config);
        $this->assertSame($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('client_config', $config);
        $this->assertSame($expectedClientConfig, $config['client_config']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertSame($expectedGetOptions, $config['get_options']);

        $this->assertArrayHasKey('put_options', $config);
        $this->assertSame($expectedObjectOptions, $config['put_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertSame($expectedCachePrefix, $config['cache_prefix']);
    }

    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs): array
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

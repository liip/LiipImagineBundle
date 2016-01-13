<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Resolver;

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory<extended>
 */
class AwsS3ResolverFactoryTest extends \Phpunit_Framework_TestCase
{
    public function testImplementsResolverFactoryInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new AwsS3ResolverFactory();
    }

    public function testReturnExpectedName()
    {
        $resolver = new AwsS3ResolverFactory();

        $this->assertEquals('aws_s3', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'theBucket',
            'acl' => 'theAcl',
            'url_options' => array('fooKey' => 'fooVal'),
            'get_options' => array(),
            'put_options' => array('barKey' => 'barVal'),
            'cache' => false,
            'proxies' => array(),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.aws_s3', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertEquals('liip_imagine.cache.resolver.theresolvername.client', $resolverDefinition->getArgument(0));

        $this->assertEquals('theBucket', $resolverDefinition->getArgument(1));
        $this->assertEquals('theAcl', $resolverDefinition->getArgument(2));
        $this->assertEquals(array('fooKey' => 'fooVal'), $resolverDefinition->getArgument(3));
        $this->assertEquals(array('barKey' => 'barVal'), $resolverDefinition->getArgument(4));
    }

    public function testOverrideDeprecatedUrlOptionsWithNewGetOptions()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'theBucket',
            'acl' => 'theAcl',
            'url_options' => array('fooKey' => 'fooVal', 'barKey' => 'barVal'),
            'get_options' => array('fooKey' => 'fooVal_overridden'),
            'put_options' => array(),
            'cache' => false,
            'proxies' => array(),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');
        $this->assertEquals(array('fooKey' => 'fooVal_overridden', 'barKey' => 'barVal'), $resolverDefinition->getArgument(3));
    }

    public function testCreateS3ClientDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array('theClientConfigKey' => 'theClientConfigVal'),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache' => false,
            'proxies' => array(),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.client'));

        $clientDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.client');
        $this->assertEquals('Aws\S3\S3Client', $clientDefinition->getClass());
        $this->assertEquals(array('theClientConfigKey' => 'theClientConfigVal'), $clientDefinition->getArgument(0));
    }

    public function testCreateS3ClientDefinitionWithFactoryOnCreate()
    {
        if (version_compare(Kernel::VERSION_ID, '20600') < 0) {
            $this->markTestSkipped('No need to test on symfony < 2.6');
        }

        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array('theClientConfigKey' => 'theClientConfigVal'),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache' => false,
            'proxies' => array(),
        ));

        $clientDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.client');
        $this->assertEquals(array('Aws\S3\S3Client', 'factory'), $clientDefinition->getFactory());
    }

    public function testLegacyCreateS3ClientDefinitionWithFactoryOnCreate()
    {
        if (version_compare(Kernel::VERSION_ID, '20600') >= 0) {
            $this->markTestSkipped('No need to test on symfony >= 2.6');
        }

        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array('theClientConfigKey' => 'theClientConfigVal'),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache' => false,
            'proxies' => array(),
        ));

        $clientDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.client');
        $this->assertEquals('Aws\S3\S3Client', $clientDefinition->getFactoryClass());
        $this->assertEquals('factory', $clientDefinition->getFactoryMethod());
    }

    public function testWrapResolverWithProxyOnCreateWithoutCache()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache' => false,
            'proxies' => array('foo'),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.proxied');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $proxiedResolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertFalse($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.cached'));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.proxy', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertEquals('liip_imagine.cache.resolver.theresolvername.proxied', $resolverDefinition->getArgument(0));

        $this->assertEquals(array('foo'), $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithCacheOnCreateWithoutProxy()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache' => 'theCacheServiceId',
            'proxies' => array(),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.cached'));
        $cachedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.cached');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $cachedResolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.aws_s3', $cachedResolverDefinition->getParent());

        $this->assertFalse($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.proxied'));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.cache', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertEquals('thecacheserviceid', $resolverDefinition->getArgument(0));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(1));
        $this->assertEquals('liip_imagine.cache.resolver.theresolvername.cached', $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithProxyAndCacheOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache' => 'theCacheServiceId',
            'proxies' => array('foo'),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.proxied');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $proxiedResolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.cached'));
        $cachedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.cached');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $cachedResolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.proxy', $cachedResolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $cachedResolverDefinition->getArgument(0));
        $this->assertEquals('liip_imagine.cache.resolver.theresolvername.proxied', $cachedResolverDefinition->getArgument(0));

        $this->assertEquals(array('foo'), $cachedResolverDefinition->getArgument(1));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.cache', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertEquals('thecacheserviceid', $resolverDefinition->getArgument(0));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(1));
        $this->assertEquals('liip_imagine.cache.resolver.theresolvername.cached', $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithProxyMatchReplaceStrategyOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache' => 'theCacheServiceId',
            'proxies' => array('foo' => 'bar'),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.proxied');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $proxiedResolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.cached'));
        $cachedResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.cached');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $cachedResolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.proxy', $cachedResolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $cachedResolverDefinition->getArgument(0));
        $this->assertEquals('liip_imagine.cache.resolver.theresolvername.proxied', $cachedResolverDefinition->getArgument(0));

        $this->assertEquals(array('foo' => 'bar'), $cachedResolverDefinition->getArgument(1));
    }

    public function testSetCachePrefixIfDefined()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'get_options' => array(),
            'put_options' => array(),
            'cache_prefix' => 'theCachePrefix',
            'cache' => null,
            'proxies' => array(),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');

        $methodCalls = $resolverDefinition->getMethodCalls();

        $this->assertCount(1, $methodCalls);
        $this->assertEquals('setCachePrefix', $methodCalls[0][0]);
        $this->assertEquals(array('theCachePrefix'), $methodCalls[0][1]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "bucket" at path "aws_s3" must be configured.
     */
    public function testThrowBucketNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "client_config" at path "aws_s3" must be configured.
     */
    public function testThrowClientConfigNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => 'aBucket',
            ),
        ));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid type for path "aws_s3.client_config". Expected array, but got string
     */
    public function testThrowClientConfigNotArrayOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => 'aBucket',
                'client_config' => 'not_array',
            ),
        ));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedClientConfig = array(
            'theKey' => 'theClientConfigVal',
            'theOtherKey' => 'theOtherClientConfigValue',
        );
        $expectedUrlOptions = array(
            'theKey' => 'theUrlOptionsVal',
            'theOtherKey' => 'theOtherUrlOptionsValue',
        );
        $expectedGetOptions = array(
            'theKey' => 'theGetOptionsVal',
            'theOtherKey' => 'theOtherGetOptionsValue',
        );
        $expectedObjectOptions = array(
            'theKey' => 'theObjectOptionsVal',
            'theOtherKey' => 'theOtherObjectOptionsValue',
        );
        $expectedBucket = 'theBucket';
        $expectedAcl = 'theAcl';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => $expectedBucket,
                'acl' => $expectedAcl,
                'client_config' => $expectedClientConfig,
                'url_options' => $expectedUrlOptions,
                'get_options' => $expectedGetOptions,
                'put_options' => $expectedObjectOptions,
                'cache_prefix' => $expectedCachePrefix,
            ),
        ));

        $this->assertArrayHasKey('bucket', $config);
        $this->assertEquals($expectedBucket, $config['bucket']);

        $this->assertArrayHasKey('acl', $config);
        $this->assertEquals($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('client_config', $config);
        $this->assertEquals($expectedClientConfig, $config['client_config']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertEquals($expectedUrlOptions, $config['url_options']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertEquals($expectedGetOptions, $config['get_options']);

        $this->assertArrayHasKey('put_options', $config);
        $this->assertEquals($expectedObjectOptions, $config['put_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertEquals($expectedCachePrefix, $config['cache_prefix']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $expectedAcl = 'public-read';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => 'aBucket',
                'client_config' => array(),
            ),
        ));

        $this->assertArrayHasKey('acl', $config);
        $this->assertEquals($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertEquals(array(), $config['url_options']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertEquals(array(), $config['get_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertNull($config['cache_prefix']);
    }

    public function testSupportAwsV3ClientConfig()
    {
        $expectedClientConfig = array(
            'credentials' => array(
                'key' => 'theKey',
                'secret' => 'theSecret',
                'token' => 'theToken',
            ),
            'region' => 'theRegion',
            'version' => 'theVersion',
        );
        $expectedUrlOptions = array(
            'theKey' => 'theUrlOptionsVal',
            'theOtherKey' => 'theOtherUrlOptionsValue',
        );
        $expectedGetOptions = array(
            'theKey' => 'theGetOptionsVal',
            'theOtherKey' => 'theOtherGetOptionsValue',
        );
        $expectedObjectOptions = array(
            'theKey' => 'theObjectOptionsVal',
            'theOtherKey' => 'theOtherObjectOptionsValue',
        );
        $expectedBucket = 'theBucket';
        $expectedAcl = 'theAcl';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => $expectedBucket,
                'acl' => $expectedAcl,
                'client_config' => $expectedClientConfig,
                'url_options' => $expectedUrlOptions,
                'get_options' => $expectedGetOptions,
                'put_options' => $expectedObjectOptions,
                'cache_prefix' => $expectedCachePrefix,
            ),
        ));

        $this->assertArrayHasKey('bucket', $config);
        $this->assertEquals($expectedBucket, $config['bucket']);

        $this->assertArrayHasKey('acl', $config);
        $this->assertEquals($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('client_config', $config);
        $this->assertEquals($expectedClientConfig, $config['client_config']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertEquals($expectedUrlOptions, $config['url_options']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertEquals($expectedGetOptions, $config['get_options']);

        $this->assertArrayHasKey('put_options', $config);
        $this->assertEquals($expectedObjectOptions, $config['put_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertEquals($expectedCachePrefix, $config['cache_prefix']);
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @param array       $configs
     *
     * @return array
     */
    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs)
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

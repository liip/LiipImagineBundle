<?php
namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Resolver;

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AwsS3ResolverFactoryTest extends \Phpunit_Framework_TestCase
{
    public function testImplementsResolverFactoryInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new AwsS3ResolverFactory;
    }

    public function testReturnExpectedName()
    {
        $resolver = new AwsS3ResolverFactory;

        $this->assertEquals('aws_s3', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate()
    {
        $container = new ContainerBuilder;

        $resolver = new AwsS3ResolverFactory;

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'theBucket',
            'acl' => 'theAcl',
            'url_options' => array('fooKey' => 'fooVal'),
            'cache' => false
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
    }

    public function testCreateS3ClientDefinitionOnCreate()
    {
        $container = new ContainerBuilder;

        $resolver = new AwsS3ResolverFactory;

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array('theClientConfigKey' => 'theClientConfigVal'),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'cache' => false
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.client'));

        $clientDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.client');
        $this->assertEquals('Aws\S3\S3Client', $clientDefinition->getClass());
        $this->assertEquals('Aws\S3\S3Client', $clientDefinition->getFactoryClass());
        $this->assertEquals('factory', $clientDefinition->getFactoryMethod());
        $this->assertEquals(array('theClientConfigKey' => 'theClientConfigVal'), $clientDefinition->getArgument(0));
    }

    public function testWrapResolverWithCacheOnCreate()
    {
        $container = new ContainerBuilder;

        $resolver = new AwsS3ResolverFactory;

        $resolver->create($container, 'theResolverName', array(
            'client_config' => array(),
            'bucket' => 'aBucket',
            'acl' => 'aAcl',
            'url_options' => array(),
            'cache' => 'theCacheServiceId',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername.internal'));
        $internalResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername.internal');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $internalResolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.aws_s3', $internalResolverDefinition->getParent());

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.cache', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertEquals('thecacheserviceid', $resolverDefinition->getArgument(0));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(1));
        $this->assertEquals('liip_imagine.cache.resolver.theresolvername.internal', $resolverDefinition->getArgument(1));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "bucket" at path "aws_s3" must be configured.
     */
    public function testThrowBucketNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory;
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "client_config" at path "aws_s3" must be configured.
     */
    public function testThrowClientConfinNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory;
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => 'aBucket',
            )
        ));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid type for path "aws_s3.client_config". Expected array, but got string
     */
    public function testThrowClientConfinNotArrayOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory;
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => 'aBucket',
                'client_config' => 'not_array',
            )
        ));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedClientConfig = array(
            'theKey' => 'theClientConfigVal',
            'theOtherKey' => 'theOtherClientConfigValue'
        );
        $expectedUrlOptions = array(
            'theKey' => 'theUrlOptionsVal',
            'theOtherKey' => 'theOtherUrlOptionsValue'
        );
        $expectedBucket = 'theBucket';
        $expectedAcl = 'theAcl';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory;
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => $expectedBucket,
                'acl' => $expectedAcl,
                'client_config' => $expectedClientConfig,
                'url_options' => $expectedUrlOptions
            )
        ));

        $this->assertArrayHasKey('bucket', $config);
        $this->assertEquals($expectedBucket, $config['bucket']);

        $this->assertArrayHasKey('acl', $config);
        $this->assertEquals($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('client_config', $config);
        $this->assertEquals($expectedClientConfig, $config['client_config']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertEquals($expectedUrlOptions, $config['url_options']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $expectedAcl = 'public-read';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory;
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'aws_s3' => array(
                'bucket' => 'aBucket',
                'client_config' => array(),
            )
        ));

        $this->assertArrayHasKey('acl', $config);
        $this->assertEquals($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertEquals(array(), $config['url_options']);
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @param array $configs
     *
     * @return array
     */
    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs)
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

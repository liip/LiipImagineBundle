<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Resolver;

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory<extended>
 */
class FlysystemResolverFactoryTest extends \Phpunit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!class_exists('\League\Flysystem\Filesystem')) {
            $this->markTestSkipped(
                'The league/flysystem PHP library is not available.'
            );
        }
    }

    public function testImplementsResolverFactoryInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new FlysystemResolverFactory();
    }

    public function testReturnExpectedName()
    {
        $resolver = new FlysystemResolverFactory();

        $this->assertEquals('flysystem', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new FlysystemResolverFactory();

        $resolver->create($container, 'theResolverName', array(
            'filesystem_service' => 'flyfilesystemservice',
            'root_url' => 'http://images.example.com',
            'cache_prefix' => 'theCachePrefix',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.theresolvername'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.flysystem', $resolverDefinition->getParent());

        $this->assertEquals('http://images.example.com', $resolverDefinition->getArgument(2));
        $this->assertEquals('theCachePrefix', $resolverDefinition->getArgument(3));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedRootUrl = 'http://images.example.com';
        $expectedCachePrefix = 'theCachePrefix';
        $expectedFlysystemService = 'flyfilesystemservice';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('flysystem', 'array');

        $resolver = new FlysystemResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'flysystem' => array(
                'root_url' => $expectedRootUrl,
                'cache_prefix' => $expectedCachePrefix,
                'filesystem_service' => $expectedFlysystemService,
            ),
        ));

        $this->assertArrayHasKey('filesystem_service', $config);
        $this->assertEquals($expectedFlysystemService, $config['filesystem_service']);

        $this->assertArrayHasKey('root_url', $config);
        $this->assertEquals($expectedRootUrl, $config['root_url']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertEquals($expectedCachePrefix, $config['cache_prefix']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('flysystem', 'array');

        $resolver = new FlysystemResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'flysystem' => array(),
        ));
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

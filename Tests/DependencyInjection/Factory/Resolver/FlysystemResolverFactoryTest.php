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

use League\Flysystem\Filesystem;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory<extended>
 */
class FlysystemResolverFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!class_exists(Filesystem::class)) {
            $this->markTestSkipped('Requires the league/flysystem package.');
        }
    }

    public function testImplementsResolverFactoryInterface()
    {
        $rc = new \ReflectionClass(FlysystemResolverFactory::class);

        $this->assertTrue($rc->implementsInterface(ResolverFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        $loader = new FlysystemResolverFactory();

        $this->assertInstanceOf(FlysystemResolverFactory::class, $loader);
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

        $resolver->create($container, 'the_resolver_name', array(
            'filesystem_service' => 'flyfilesystemservice',
            'root_url' => 'http://images.example.com',
            'cache_prefix' => 'theCachePrefix',
            'visibility' => 'public',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.flysystem', $resolverDefinition->getParent());

        $this->assertEquals('http://images.example.com', $resolverDefinition->getArgument(2));
        $this->assertEquals('theCachePrefix', $resolverDefinition->getArgument(3));
        $this->assertEquals('public', $resolverDefinition->getArgument(4));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedRootUrl = 'http://images.example.com';
        $expectedCachePrefix = 'theCachePrefix';
        $expectedFlysystemService = 'flyfilesystemservice';
        $expectedVisibility = 'public';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('flysystem', 'array');

        $resolver = new FlysystemResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'flysystem' => array(
                'root_url' => $expectedRootUrl,
                'cache_prefix' => $expectedCachePrefix,
                'filesystem_service' => $expectedFlysystemService,
                'visibility' => 'public',
            ),
        ));

        $this->assertArrayHasKey('filesystem_service', $config);
        $this->assertEquals($expectedFlysystemService, $config['filesystem_service']);

        $this->assertArrayHasKey('root_url', $config);
        $this->assertEquals($expectedRootUrl, $config['root_url']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertEquals($expectedCachePrefix, $config['cache_prefix']);

        $this->assertArrayHasKey('visibility', $config);
        $this->assertEquals($expectedVisibility, $config['visibility']);
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

        $this->processConfigTree($treeBuilder, array(
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

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

use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory<extended>
 */
class FlysystemResolverFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!interface_exists(FilesystemInterface::class)) {
            $this->markTestSkipped('Requires the league/flysystem:^1.0 package.');
        }
    }

    public function testImplementsResolverFactoryInterface(): void
    {
        $rc = new \ReflectionClass(FlysystemResolverFactory::class);

        $this->assertTrue($rc->implementsInterface(ResolverFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments(): void
    {
        $loader = new FlysystemResolverFactory();

        $this->assertInstanceOf(FlysystemResolverFactory::class, $loader);
    }

    public function testReturnExpectedName(): void
    {
        $resolver = new FlysystemResolverFactory();

        $this->assertSame('flysystem', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = new FlysystemResolverFactory();

        $resolver->create($container, 'the_resolver_name', [
            'filesystem_service' => 'flyfilesystemservice',
            'root_url' => 'http://images.example.com',
            'cache_prefix' => 'theCachePrefix',
            'visibility' => 'public',
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame('liip_imagine.cache.resolver.prototype.flysystem', $resolverDefinition->getParent());

        $this->assertSame('http://images.example.com', $resolverDefinition->getArgument(2));
        $this->assertSame('theCachePrefix', $resolverDefinition->getArgument(3));
        $this->assertSame('public', $resolverDefinition->getArgument(4));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration(): void
    {
        $expectedRootUrl = 'http://images.example.com';
        $expectedCachePrefix = 'theCachePrefix';
        $expectedFlysystemService = 'flyfilesystemservice';
        $expectedVisibility = 'public';

        $treeBuilder = new TreeBuilder('flysystem');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('flysystem');

        $resolver = new FlysystemResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'flysystem' => [
                'root_url' => $expectedRootUrl,
                'cache_prefix' => $expectedCachePrefix,
                'filesystem_service' => $expectedFlysystemService,
                'visibility' => 'public',
            ],
        ]);

        $this->assertArrayHasKey('filesystem_service', $config);
        $this->assertSame($expectedFlysystemService, $config['filesystem_service']);

        $this->assertArrayHasKey('root_url', $config);
        $this->assertSame($expectedRootUrl, $config['root_url']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertSame($expectedCachePrefix, $config['cache_prefix']);

        $this->assertArrayHasKey('visibility', $config);
        $this->assertSame($expectedVisibility, $config['visibility']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $treeBuilder = new TreeBuilder('flysystem');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('flysystem');

        $resolver = new FlysystemResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, [
            'flysystem' => [],
        ]);
    }

    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs): array
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

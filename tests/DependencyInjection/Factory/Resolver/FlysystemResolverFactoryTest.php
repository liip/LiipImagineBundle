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

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory<extended>
 */
class FlysystemResolverFactoryTest extends TestCase
{
    private const CACHE_PREFIX = 'theCachePrefix';
    private const CACHE_SERVICE_ID = 'the_cache_service_id';
    private const CACHED_RESOLVER_ID = 'liip_imagine.cache.resolver.the_resolver_name.cached';
    private const FILESYSTEM_SERVICE_ID = 'flyfilesystemservice';
    private const FLYSYSTEM_RESOLVER_PARENT = 'liip_imagine.cache.resolver.prototype.flysystem2';
    private const PSR_CACHE_RESOLVER_PARENT = 'liip_imagine.cache.resolver.prototype.psr_cache';
    private const RESOLVER_ID = 'liip_imagine.cache.resolver.the_resolver_name';
    private const RESOLVER_NAME = 'the_resolver_name';
    private const RESOLVER_TAG = 'liip_imagine.cache.resolver';
    private const ROOT_URL = 'http://images.example.com';

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

        $resolver->create($container, self::RESOLVER_NAME, [
            'filesystem_service' => self::FILESYSTEM_SERVICE_ID,
            'root_url' => self::ROOT_URL,
            'cache_prefix' => self::CACHE_PREFIX,
            'visibility' => 'public',
            'cache' => false,
        ]);

        $this->assertTrue($container->hasDefinition(self::RESOLVER_ID));

        $resolverDefinition = $container->getDefinition(self::RESOLVER_ID);
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame(self::FLYSYSTEM_RESOLVER_PARENT, $resolverDefinition->getParent());

        $this->assertSame(self::ROOT_URL, $resolverDefinition->getArgument(2));
        $this->assertSame(self::CACHE_PREFIX, $resolverDefinition->getArgument(3));
        $this->assertSame('public', $resolverDefinition->getArgument(4));
    }

    public function testWrapResolverWithPsrCacheOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = new FlysystemResolverFactory();

        $resolver->create($container, self::RESOLVER_NAME, [
            'filesystem_service' => self::FILESYSTEM_SERVICE_ID,
            'root_url' => self::ROOT_URL,
            'cache_prefix' => self::CACHE_PREFIX,
            'visibility' => 'public',
            'cache' => self::CACHE_SERVICE_ID,
        ]);

        $this->assertTrue($container->hasDefinition(self::CACHED_RESOLVER_ID));
        $cachedResolverDefinition = $container->getDefinition(self::CACHED_RESOLVER_ID);
        $this->assertInstanceOf(ChildDefinition::class, $cachedResolverDefinition);
        $this->assertSame(self::FLYSYSTEM_RESOLVER_PARENT, $cachedResolverDefinition->getParent());

        $resolverDefinition = $container->getDefinition(self::RESOLVER_ID);
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame(self::PSR_CACHE_RESOLVER_PARENT, $resolverDefinition->getParent());

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(0));
        $this->assertSame(self::CACHE_SERVICE_ID, (string) $resolverDefinition->getArgument(0));

        $this->assertInstanceOf(Reference::class, $resolverDefinition->getArgument(1));
        $this->assertSame(self::CACHED_RESOLVER_ID, (string) $resolverDefinition->getArgument(1));
        $this->assertSame([['resolver' => self::RESOLVER_NAME]], $resolverDefinition->getTag(self::RESOLVER_TAG));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration(): void
    {
        $expectedRootUrl = self::ROOT_URL;
        $expectedCachePrefix = self::CACHE_PREFIX;
        $expectedFlysystemService = self::FILESYSTEM_SERVICE_ID;
        $expectedVisibility = 'public';

        $treeBuilder = new TreeBuilder('flysystem');
        $resolver = new FlysystemResolverFactory();
        $resolver->addConfiguration($treeBuilder->getRootNode());

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

        $this->assertArrayHasKey('cache', $config);
        $this->assertFalse($config['cache']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $treeBuilder = new TreeBuilder('flysystem');
        $resolver = new FlysystemResolverFactory();
        $resolver->addConfiguration($treeBuilder->getRootNode());

        $this->processConfigTree($treeBuilder, [
            'flysystem' => [],
        ]);
    }

    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs): array
    {
        return (new Processor())->process($treeBuilder->buildTree(), $configs);
    }
}

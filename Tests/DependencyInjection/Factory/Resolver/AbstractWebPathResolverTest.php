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

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AbstractWebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AbstractWebPathResolverFactory
 */
abstract class AbstractWebPathResolverTest extends TestCase
{
    public function testImplementsResolverFactoryInterface(): void
    {
        $rc = new \ReflectionClass($this->getClassName());

        $this->assertTrue($rc->implementsInterface(ResolverFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments(): void
    {
        $loader = $this->createResolver();

        $this->assertInstanceOf($this->getClassName(), $loader);
    }

    public function testAbstractWebPathResolverFactoryImplementation(): void
    {
        $this->assertTrue(is_a($this->getClassName(), AbstractWebPathResolverFactory::class, true));
    }

    public function testCreateResolverDefinitionOnCreate(): void
    {
        $container = new ContainerBuilder();

        $resolver = $this->createResolver();
        $resolver->create(
            $container,
            'the_resolver_name',
            [
                'web_root' => 'theWebRoot',
                'cache_prefix' => 'theCachePrefix',
            ]
        );

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertSame(
            sprintf('liip_imagine.cache.resolver.prototype.%s', $resolver->getName()),
            $resolverDefinition->getParent()
        );

        $utilPathResolverReference = $resolverDefinition->getArgument(1);
        $this->assertInstanceOf(Reference::class, $utilPathResolverReference);

        $utilPathResolverServiceId = (string) $utilPathResolverReference;
        $this->assertSame('liip_imagine.util.resolver.path', $utilPathResolverServiceId);
        $this->assertTrue($container->hasDefinition($utilPathResolverServiceId));

        $utilPathResolverDefinition = $container->getDefinition($utilPathResolverServiceId);
        $this->assertInstanceOf(ChildDefinition::class, $utilPathResolverDefinition);
        $this->assertSame('liip_imagine.util.resolver.prototype.path', $utilPathResolverDefinition->getParent());

        $this->assertSame('theWebRoot', $utilPathResolverDefinition->getArgument(0));
        $this->assertSame('theCachePrefix', $utilPathResolverDefinition->getArgument(1));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration(): void
    {
        $expectedWebPath = 'theWebPath';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder('test_resolver_name');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('test_resolver_name');

        $resolver = $this->createResolver();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree(
            $treeBuilder,
            [
                $resolver->getName() => [
                    'web_root' => $expectedWebPath,
                    'cache_prefix' => $expectedCachePrefix,
                ],
            ]
        );

        $this->assertArrayHasKey('web_root', $config);
        $this->assertSame($expectedWebPath, $config['web_root']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertSame($expectedCachePrefix, $config['cache_prefix']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration(): void
    {
        $treeBuilder = new TreeBuilder('test_resolver_name');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('test_resolver_name');

        $resolver = $this->createResolver();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree(
            $treeBuilder,
            [
                $resolver->getName() => [],
            ]
        );

        $this->assertArrayHasKey('web_root', $config);
        $this->assertSame(SymfonyFramework::getContainerResolvableRootWebPath(), $config['web_root']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertSame('media/cache', $config['cache_prefix']);
    }

    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs): array
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }

    /**
     * @return string|ResolverFactoryInterface
     */
    abstract protected function getClassName();

    private function createResolver()
    {
        $className = $this->getClassName();

        return new $className();
    }
}

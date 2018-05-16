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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AbstractWebPathResolverFactory
 */
abstract class AbstractWebPathResolverTest extends TestCase
{
    public function testImplementsResolverFactoryInterface()
    {
        $rc = new \ReflectionClass($this->getClassName());
        
        $this->assertTrue($rc->implementsInterface(ResolverFactoryInterface::class));
    }
    
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        $loader = $this->createResolver();
        
        $this->assertInstanceOf($this->getClassName(), $loader);
    }
    
    public function testAbstractWebPathResolverFactoryImplementation()
    {
        $this->assertTrue(is_a($this->getClassName(), AbstractWebPathResolverFactory::class, true));
    }
    
    public function testCreateResolverDefinitionOnCreate()
    {
        $container = new ContainerBuilder();
        
        $resolver = $this->createResolver();
        $resolver->create(
            $container,
            'the_resolver_name',
            [
                'web_root'     => 'theWebRoot',
                'cache_prefix' => 'theCachePrefix',
            ]
        );
        
        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));
        
        /**
         * @var ChildDefinition $resolverDefinition
         */
        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertEquals(
            sprintf('liip_imagine.cache.resolver.prototype.%s', $resolver->getName()),
            $resolverDefinition->getParent()
        );
        
        /**
         * @var Reference $utilPathResolverReference
         */
        $utilPathResolverReference = $resolverDefinition->getArgument(1);
        $this->assertInstanceOf(Reference::class, $utilPathResolverReference);
        
        $utilPathResolverServiceId = $utilPathResolverReference->__toString();
        $this->assertEquals('liip_imagine.util.resolver.path', $utilPathResolverServiceId);
        $this->assertTrue($container->hasDefinition($utilPathResolverServiceId));
        /**
         * @var ChildDefinition $utilPathResolverDefinition
         */
        $utilPathResolverDefinition = $container->getDefinition($utilPathResolverServiceId);
        $this->assertInstanceOf(ChildDefinition::class, $utilPathResolverDefinition);
        $this->assertEquals('liip_imagine.util.resolver.prototype.path', $utilPathResolverDefinition->getParent());
        
        $this->assertEquals('theWebRoot', $utilPathResolverDefinition->getArgument(0));
        $this->assertEquals('theCachePrefix', $utilPathResolverDefinition->getArgument(1));
    }
    
    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedWebPath = 'theWebPath';
        $expectedCachePrefix = 'theCachePrefix';
        
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('test_resolver_name', 'array');
        
        $resolver = $this->createResolver();
        $resolver->addConfiguration($rootNode);
        
        $config = $this->processConfigTree(
            $treeBuilder,
            [
                $resolver->getName() => [
                    'web_root'     => $expectedWebPath,
                    'cache_prefix' => $expectedCachePrefix,
                ],
            ]
        );
        
        $this->assertArrayHasKey('web_root', $config);
        $this->assertEquals($expectedWebPath, $config['web_root']);
        
        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertEquals($expectedCachePrefix, $config['cache_prefix']);
    }
    
    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('test_resolver_name', 'array');
        
        $resolver = $this->createResolver();
        $resolver->addConfiguration($rootNode);
        
        $config = $this->processConfigTree(
            $treeBuilder,
            [
                $resolver->getName() => [],
            ]
        );
        
        $this->assertArrayHasKey('web_root', $config);
        $this->assertEquals(SymfonyFramework::getContainerResolvableRootWebPath(), $config['web_root']);
        
        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertEquals('media/cache', $config['cache_prefix']);
    }
    
    /**
     * @param TreeBuilder $treeBuilder
     * @param array       $configs
     *
     * @return array
     */
    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs)
    {
        $processor = new Processor;
        
        return $processor->process($treeBuilder->buildTree(), $configs);
    }
    
    /**
     * @return string|ResolverFactoryInterface
     */
    abstract protected function getClassName();
    
    private function createResolver()
    {
        $className = $this->getClassName();
        
        return new $className;
    }
}

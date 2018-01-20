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

use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory<extended>
 */
class WebPathResolverFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testImplementsResolverFactoryInterface()
    {
        $rc = new \ReflectionClass(WebPathResolverFactory::class);

        $this->assertTrue($rc->implementsInterface(ResolverFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        $loader = new WebPathResolverFactory();

        $this->assertInstanceOf(WebPathResolverFactory::class, $loader);
    }

    public function testReturnExpectedName()
    {
        $resolver = new WebPathResolverFactory();

        $this->assertEquals('web_path', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new WebPathResolverFactory();

        $resolver->create($container, 'the_resolver_name', array(
            'web_root' => 'theWebRoot',
            'cache_prefix' => 'theCachePrefix',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.cache.resolver.the_resolver_name'));

        $resolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.the_resolver_name');
        $this->assertInstanceOf(ChildDefinition::class, $resolverDefinition);
        $this->assertEquals('liip_imagine.cache.resolver.prototype.web_path', $resolverDefinition->getParent());

        $this->assertEquals('theWebRoot', $resolverDefinition->getArgument(2));
        $this->assertEquals('theCachePrefix', $resolverDefinition->getArgument(3));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedWebPath = 'theWebPath';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('web_path', 'array');

        $resolver = new WebPathResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'web_path' => array(
                'web_root' => $expectedWebPath,
                'cache_prefix' => $expectedCachePrefix,
            ),
        ));

        $this->assertArrayHasKey('web_root', $config);
        $this->assertEquals($expectedWebPath, $config['web_root']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertEquals($expectedCachePrefix, $config['cache_prefix']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('web_path', 'array');

        $resolver = new WebPathResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'web_path' => array(),
        ));

        $this->assertArrayHasKey('web_root', $config);
        $this->assertEquals('%kernel.root_dir%/../public', $config['web_root']);

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
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

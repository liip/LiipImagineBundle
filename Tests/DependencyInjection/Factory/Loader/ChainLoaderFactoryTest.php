<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Loader;

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\ChainLoaderFactory;
use Liip\ImagineBundle\Tests\DependencyInjection\Factory\FactoryTestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Loader\ChainLoaderFactory
 */
class ChainLoaderFactoryTest extends FactoryTestCase
{
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new ChainLoaderFactory();
    }

    public function testImplementsLoaderFactoryInterface()
    {
        $this->assertInstanceOf('\Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface', new ChainLoaderFactory());
    }

    public function testReturnsExpectedName()
    {
        $loader = new ChainLoaderFactory();

        $this->assertEquals('chain', $loader->getName());
    }

    public function testCreateLoaderDefinition()
    {
        $container = new ContainerBuilder();

        $loader = new ChainLoaderFactory();
        $loader->create($container, 'the_loader_name', array(
            'loaders' => array(
                'foo',
                'bar',
                'baz',
            ),
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.the_loader_name'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.the_loader_name');

        $this->assertInstanceOfChildDefinition($loaderDefinition);
        $this->assertEquals('liip_imagine.binary.loader.prototype.chain', $loaderDefinition->getParent());

        foreach ($loaderDefinition->getArgument(0) as $reference) {
            $this->assertInstanceOf('\Symfony\Component\DependencyInjection\Reference', $reference);
        }
    }

    public function testProcessOptionsOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('chain', 'array');

        $loader = new ChainLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'chain' => array(
                'loaders' => array(
                    'foo',
                    'bar',
                ),
            ),
        ));

        $this->assertArrayHasKey('loaders', $config);
        $this->assertSame(array('foo', 'bar'), $config['loaders']);
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @param array       $configs
     *
     * @return array
     */
    private function processConfigTree(TreeBuilder $treeBuilder, array $configs)
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

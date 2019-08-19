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
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\Tests\DependencyInjection\Factory\FactoryTestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Loader\ChainLoaderFactory
 */
class ChainLoaderFactoryTest extends FactoryTestCase
{
    public function testImplementsLoaderFactoryInterface(): void
    {
        $this->assertInstanceOf(LoaderFactoryInterface::class, new ChainLoaderFactory());
    }

    public function testReturnsExpectedName(): void
    {
        $this->assertSame('chain', (new ChainLoaderFactory())->getName());
    }

    public function testCreateLoaderDefinition(): void
    {
        $container = new ContainerBuilder();

        $loader = new ChainLoaderFactory();
        $loader->create($container, 'the_loader_name', [
            'loaders' => [
                'foo',
                'bar',
                'baz',
            ],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.the_loader_name'));

        /** @var ChildDefinition $loaderDefinition */
        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.the_loader_name');

        $this->assertInstanceOfChildDefinition($loaderDefinition);
        $this->assertSame('liip_imagine.binary.loader.prototype.chain', $loaderDefinition->getParent());

        foreach ($loaderDefinition->getArgument(0) as $reference) {
            $this->assertInstanceOf('\Symfony\Component\DependencyInjection\Reference', $reference);
        }
    }

    public function testProcessOptionsOnAddConfiguration(): void
    {
        $treeBuilder = new TreeBuilder('chain');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('chain');

        $loader = new ChainLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'chain' => [
                'loaders' => [
                    'foo',
                    'bar',
                ],
            ],
        ]);

        $this->assertArrayHasKey('loaders', $config);
        $this->assertSame(['foo', 'bar'], $config['loaders']);
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @param array       $configs
     *
     * @return array
     */
    private function processConfigTree(TreeBuilder $treeBuilder, array $configs): array
    {
        return (new Processor())->process($treeBuilder->buildTree(), $configs);
    }
}

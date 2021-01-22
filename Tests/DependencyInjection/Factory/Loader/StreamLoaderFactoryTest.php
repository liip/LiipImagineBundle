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

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory
 */
class StreamLoaderFactoryTest extends TestCase
{
    public function testImplementsLoaderFactoryInterface(): void
    {
        $rc = new \ReflectionClass(StreamLoaderFactory::class);

        $this->assertTrue($rc->implementsInterface(LoaderFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments(): void
    {
        $loader = new StreamLoaderFactory();

        $this->assertInstanceOf(StreamLoaderFactory::class, $loader);
    }

    public function testReturnExpectedName(): void
    {
        $loader = new StreamLoaderFactory();

        $this->assertSame('stream', $loader->getName());
    }

    public function testCreateLoaderDefinitionOnCreate(): void
    {
        $container = new ContainerBuilder();

        $loader = new StreamLoaderFactory();

        $loader->create($container, 'the_loader_name', [
            'wrapper' => 'theWrapper',
            'context' => 'theContext',
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.the_loader_name'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.the_loader_name');
        $this->assertInstanceOf(ChildDefinition::class, $loaderDefinition);
        $this->assertSame('liip_imagine.binary.loader.prototype.stream', $loaderDefinition->getParent());

        $this->assertSame('theWrapper', $loaderDefinition->getArgument(0));
        $this->assertSame('theContext', $loaderDefinition->getArgument(1));
    }

    public function testThrowIfWrapperNotSetOnAddConfiguration(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/^The child (node|config) "wrapper" (at path|under) "stream" must be configured\.$/');

        $treeBuilder = new TreeBuilder('stream');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('stream');

        $resolver = new StreamLoaderFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, []);
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration(): void
    {
        $expectedWrapper = 'theWrapper';
        $expectedContext = 'theContext';

        $treeBuilder = new TreeBuilder('stream');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('stream');

        $loader = new StreamLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'stream' => [
                'wrapper' => $expectedWrapper,
                'context' => $expectedContext,
            ],
        ]);

        $this->assertArrayHasKey('wrapper', $config);
        $this->assertSame($expectedWrapper, $config['wrapper']);

        $this->assertArrayHasKey('context', $config);
        $this->assertSame($expectedContext, $config['context']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration(): void
    {
        $treeBuilder = new TreeBuilder('stream');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('stream');

        $loader = new StreamLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'stream' => [
                'wrapper' => 'aWrapper',
            ],
        ]);

        $this->assertArrayHasKey('context', $config);
        $this->assertNull($config['context']);
    }

    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs): array
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

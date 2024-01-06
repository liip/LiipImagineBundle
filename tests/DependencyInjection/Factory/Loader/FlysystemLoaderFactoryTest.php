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

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory<extended>
 */
class FlysystemLoaderFactoryTest extends AbstractTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testImplementsLoaderFactoryInterface(): void
    {
        $rc = new \ReflectionClass(FlysystemLoaderFactory::class);

        $this->assertTrue($rc->implementsInterface(LoaderFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments(): void
    {
        $loader = new FlysystemLoaderFactory();

        $this->assertInstanceOf(FlysystemLoaderFactory::class, $loader);
    }

    public function testReturnExpectedName(): void
    {
        $loader = new FlysystemLoaderFactory();

        $this->assertSame('flysystem', $loader->getName());
    }

    public function testCreateLoaderDefinitionOnCreate(): void
    {
        $container = new ContainerBuilder();

        $loader = new FlysystemLoaderFactory();

        $loader->create($container, 'the_loader_name', [
            'filesystem_service' => 'flyfilesystemservice',
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.the_loader_name'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.the_loader_name');
        $this->assertInstanceOf(ChildDefinition::class, $loaderDefinition);
        $loaderName = 'liip_imagine.binary.loader.prototype.flysystem2';
        $this->assertSame($loaderName, $loaderDefinition->getParent());

        $reference = $loaderDefinition->getArgument(1);
        $this->assertSame('flyfilesystemservice', (string) $reference);
    }

    public function testThrowIfFileSystemServiceNotSetOnAddConfiguration(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/^The child (node|config) "filesystem_service" (at path|under) "flysystem" must be configured\.$/');

        $treeBuilder = new TreeBuilder('flysystem');
        $resolver = new FlysystemLoaderFactory();
        $resolver->addConfiguration($treeBuilder->getRootNode());

        $this->processConfigTree($treeBuilder, []);
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration(): void
    {
        $expectedService = 'theService';

        $treeBuilder = new TreeBuilder('flysystem');
        $loader = new FlysystemLoaderFactory();
        $loader->addConfiguration($treeBuilder->getRootNode());

        $config = $this->processConfigTree($treeBuilder, [
            'flysystem' => [
                'filesystem_service' => $expectedService,
            ],
        ]);

        $this->assertArrayHasKey('filesystem_service', $config);
        $this->assertSame($expectedService, $config['filesystem_service']);
    }

    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs): array
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}

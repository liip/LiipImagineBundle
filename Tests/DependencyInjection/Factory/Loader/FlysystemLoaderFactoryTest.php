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

use League\Flysystem\Filesystem;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;

/**
 * @requires PHP 5.4
 *
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory<extended>
 */
class FlysystemLoaderFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!class_exists(Filesystem::class)) {
            $this->markTestSkipped('Requires the league/flysystem package.');
        }
    }

    public function testImplementsLoaderFactoryInterface()
    {
        $rc = new \ReflectionClass(FlysystemLoaderFactory::class);

        $this->assertTrue($rc->implementsInterface(LoaderFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        $loader = new FlysystemLoaderFactory();

        $this->assertInstanceOf(FlysystemLoaderFactory::class, $loader);
    }

    public function testReturnExpectedName()
    {
        $loader = new FlysystemLoaderFactory();

        $this->assertEquals('flysystem', $loader->getName());
    }

    public function testCreateLoaderDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $loader = new FlysystemLoaderFactory();

        $loader->create($container, 'the_loader_name', array(
            'filesystem_service' => 'flyfilesystemservice',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.the_loader_name'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.the_loader_name');
        $this->assertInstanceOf(ChildDefinition::class, $loaderDefinition);
        $this->assertEquals('liip_imagine.binary.loader.prototype.flysystem', $loaderDefinition->getParent());

        $reference = $loaderDefinition->getArgument(1);
        $this->assertEquals('flyfilesystemservice', "$reference");
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "filesystem_service" at path "flysystem" must be configured.
     */
    public function testThrowIfFileSystemServiceNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('flysystem', 'array');

        $resolver = new FlysystemLoaderFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array());
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedService = 'theService';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('flysystem', 'array');

        $loader = new FlysystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'flysystem' => array(
                'filesystem_service' => $expectedService,
            ),
        ));

        $this->assertArrayHasKey('filesystem_service', $config);
        $this->assertEquals($expectedService, $config['filesystem_service']);
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

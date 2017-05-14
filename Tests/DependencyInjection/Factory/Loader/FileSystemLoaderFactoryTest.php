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

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\Tests\DependencyInjection\Factory\FactoryTestCase;
use Liip\ImagineBundle\Tests\Functional\Fixtures\BarBundle\LiipBarBundle;
use Liip\ImagineBundle\Tests\Functional\Fixtures\FooBundle\LiipFooBundle;
use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory<extended>
 */
class FileSystemLoaderFactoryTest extends FactoryTestCase
{
    public function testImplementsLoaderFactoryInterface()
    {
        $rc = new \ReflectionClass(FileSystemLoaderFactory::class);

        $this->assertTrue($rc->implementsInterface(LoaderFactoryInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        $loader = new FileSystemLoaderFactory();

        $this->assertInstanceOf(FileSystemLoaderFactory::class, $loader);
    }

    public function testReturnExpectedName()
    {
        $loader = new FileSystemLoaderFactory();

        $this->assertSame('filesystem', $loader->getName());
    }

    public function testCreateLoaderDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $loader = new FileSystemLoaderFactory();
        $loader->create($container, 'the_loader_name', [
            'data_root' => ['theDataRoot'],
            'locator' => 'filesystem',
            'bundle_resources' => [
                'enabled' => false,
                'access_control_type' => 'blacklist',
                'access_control_list' => [],
            ],
        ]);

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.the_loader_name'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.the_loader_name');

        $this->assertInstanceOfChildDefinition($loaderDefinition);
        $this->assertSame('liip_imagine.binary.loader.prototype.filesystem', $loaderDefinition->getParent());

        $this->assertSame(['theDataRoot'], $loaderDefinition->getArgument(2)->getArgument(0));
    }

    public function testCreateLoaderDefinitionOnCreateWithBundlesEnabledUsingMetadata()
    {
        $fooBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/FooBundle');
        $barBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/BarBundle');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles_metadata', [
            'LiipFooBundle' => [
                'path' => $fooBundleRootPath,
            ],
            'LiipBarBundle' => [
                'path' => $barBundleRootPath,
            ],
        ]);

        $loader = new FileSystemLoaderFactory();
        $loader->create($container, 'the_loader_name', [
            'data_root' => ['theDataRoot'],
            'locator' => 'filesystem',
            'bundle_resources' => [
                'enabled' => true,
                'access_control_type' => 'blacklist',
                'access_control_list' => [],
            ],
        ]);

        $expected = [
            'theDataRoot',
            'LiipFooBundle' => $fooBundleRootPath.'/Resources/public',
            'LiipBarBundle' => $barBundleRootPath.'/Resources/public',
        ];

        $this->assertSame($expected, $container->getDefinition('liip_imagine.binary.loader.the_loader_name')->getArgument(2)->getArgument(0));
    }

    public function testCreateLoaderDefinitionOnCreateWithBundlesEnabledUsingMetadataAndBlacklisting()
    {
        $fooBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/FooBundle');
        $barBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/BarBundle');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles_metadata', [
            'LiipFooBundle' => [
                'path' => $fooBundleRootPath,
            ],
            'LiipBarBundle' => [
                'path' => $barBundleRootPath,
            ],
        ]);

        $loader = new FileSystemLoaderFactory();
        $loader->create($container, 'the_loader_name', [
            'data_root' => ['theDataRoot'],
            'locator' => 'filesystem',
            'bundle_resources' => [
                'enabled' => true,
                'access_control_type' => 'blacklist',
                'access_control_list' => [
                    'LiipFooBundle',
                ],
            ],
        ]);

        $expected = [
            'theDataRoot',
            'LiipBarBundle' => $barBundleRootPath.'/Resources/public',
        ];

        $this->assertSame($expected, $container->getDefinition('liip_imagine.binary.loader.the_loader_name')->getArgument(2)->getArgument(0));
    }

    public function testCreateLoaderDefinitionOnCreateWithBundlesEnabledUsingMetadataAndWhitelisting()
    {
        $fooBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/FooBundle');
        $barBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/BarBundle');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles_metadata', [
            'LiipFooBundle' => [
                'path' => $fooBundleRootPath,
            ],
            'LiipBarBundle' => [
                'path' => $barBundleRootPath,
            ],
        ]);

        $loader = new FileSystemLoaderFactory();
        $loader->create($container, 'the_loader_name', [
            'data_root' => ['theDataRoot'],
            'locator' => 'filesystem',
            'bundle_resources' => [
                'enabled' => true,
                'access_control_type' => 'whitelist',
                'access_control_list' => [
                    'LiipFooBundle',
                ],
            ],
        ]);

        $expected = [
            'theDataRoot',
            'LiipFooBundle' => $fooBundleRootPath.'/Resources/public',
        ];

        $this->assertSame($expected, $container->getDefinition('liip_imagine.binary.loader.the_loader_name')->getArgument(2)->getArgument(0));
    }

    public function testCreateLoaderDefinitionOnCreateWithBundlesEnabledUsingNamedObj()
    {
        $fooBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/FooBundle');
        $barBundleRootPath = realpath(__DIR__.'/../../../Functional/Fixtures/BarBundle');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', [
            LiipFooBundle::class,
            LiipBarBundle::class,
        ]);

        $loader = new FileSystemLoaderFactory();
        $loader->create($container, 'the_loader_name', [
            'data_root' => ['theDataRoot'],
            'locator' => 'filesystem',
            'bundle_resources' => [
                'enabled' => true,
                'access_control_type' => 'blacklist',
                'access_control_list' => [],
            ],
        ]);

        $expected = [
            'theDataRoot',
            'LiipFooBundle' => $fooBundleRootPath.'/Resources/public',
            'LiipBarBundle' => $barBundleRootPath.'/Resources/public',
        ];

        $this->assertSame($expected, $container->getDefinition('liip_imagine.binary.loader.the_loader_name')->getArgument(2)->getArgument(0));
    }

    public function testAbleToCreateTwoDistinctLoaders()
    {
        $container = new ContainerBuilder();

        $loader = new FileSystemLoaderFactory();
        $loader->create($container, 'first_loader', array(
            'data_root' => array('firstLoaderDataroot'),
            'locator' => 'filesystem',
            'bundle_resources' => array(
                'enabled' => false,
            ),
        ));

        $loader->create($container, 'second_loader', array(
            'data_root' => array('secondLoaderDataroot'),
            'locator' => 'filesystem',
            'bundle_resources' => array(
                'enabled' => false,
            ),
        ));

        $this->assertEquals(
            array('firstLoaderDataroot'),
            $container->getDefinition('liip_imagine.binary.loader.first_loader')->getArgument(2)->getArgument(0)
        );

        $this->assertEquals(
            array('secondLoaderDataroot'),
            $container->getDefinition('liip_imagine.binary.loader.second_loader')->getArgument(2)->getArgument(0)
        );
    }

    public function testThrowsExceptionOnCreateWithBundlesEnabledUsingInvalidNamedObj()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to resolve bundle "ThisBundleDoesNotExistPleaseNoOneNameTheirObjectThisInThisScopeOrTheGlobalScopeIMeanAreYouInsane" while auto-registering bundle resource paths');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', [
            'ThisBundleDoesNotExistPleaseNoOneNameTheirObjectThisInThisScopeOrTheGlobalScopeIMeanAreYouInsane',
        ]);

        $loader = new FileSystemLoaderFactory();
        $loader->create($container, 'the_loader_name', [
            'data_root' => ['theDataRoot'],
            'locator' => 'filesystem',
            'bundle_resources' => [
                'enabled' => true,
            ],
        ]);
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedDataRoot = ['theDataRoot'];

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filesystem', 'array');

        $loader = new FileSystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'filesystem' => [
                'data_root' => $expectedDataRoot,
            ],
        ]);

        $this->assertArrayHasKey('data_root', $config);
        $this->assertSame($expectedDataRoot, $config['data_root']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $expectedDataRoot = [SymfonyFramework::getContainerResolvableRootWebPath()];

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filesystem', 'array');

        $loader = new FileSystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'filesystem' => [],
        ]);

        $this->assertArrayHasKey('data_root', $config);
        $this->assertSame($expectedDataRoot, $config['data_root']);
    }

    public function testAddAsScalarExpectingArrayNormalizationOfConfiguration()
    {
        $expectedDataRoot = [SymfonyFramework::getContainerResolvableRootWebPath()];

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filesystem', 'array');

        $loader = new FileSystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'filesystem' => [
                'data_root' => $expectedDataRoot[0],
            ],
        ]);

        $this->assertArrayHasKey('data_root', $config);
        $this->assertSame($expectedDataRoot, $config['data_root']);
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

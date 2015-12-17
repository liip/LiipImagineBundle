<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Loader;

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @requires PHP 5.4
 * @covers Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory<extended>
 */
class FlysystemLoaderFactoryTest extends \Phpunit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!class_exists('\League\Flysystem\Filesystem')) {
            $this->markTestSkipped(
              'The league/flysystem PHP library is not available.'
            );
        }
    }

    public function testImplementsLoaderFactoryInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new FlysystemLoaderFactory();
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

        $loader->create($container, 'theLoaderName', array(
            'filesystem_service' => 'flyfilesystemservice',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.theloadername'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.theloadername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $loaderDefinition);
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

<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Loader;

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory<extended>
 */
class FileSystemLoaderFactoryTest extends \Phpunit_Framework_TestCase
{
    public function testImplementsLoaderFactoryInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new FileSystemLoaderFactory();
    }

    public function testReturnExpectedName()
    {
        $loader = new FileSystemLoaderFactory();

        $this->assertEquals('filesystem', $loader->getName());
    }

    public function testCreateLoaderDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $loader = new FileSystemLoaderFactory();

        $loader->create($container, 'theLoaderName', array(
            'data_root' => 'theDataRoot',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.theloadername'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.theloadername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $loaderDefinition);
        $this->assertEquals('liip_imagine.binary.loader.prototype.filesystem', $loaderDefinition->getParent());

        $this->assertEquals('theDataRoot', $loaderDefinition->getArgument(2));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedDataRoot = 'theDataRoot';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filesystem', 'array');

        $loader = new FileSystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'filesystem' => array(
                'data_root' => $expectedDataRoot,
            ),
        ));

        $this->assertArrayHasKey('data_root', $config);
        $this->assertEquals($expectedDataRoot, $config['data_root']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $expectedDataRoot = '%kernel.root_dir%/../web';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filesystem', 'array');

        $loader = new FileSystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'filesystem' => array(),
        ));

        $this->assertArrayHasKey('data_root', $config);
        $this->assertEquals($expectedDataRoot, $config['data_root']);
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

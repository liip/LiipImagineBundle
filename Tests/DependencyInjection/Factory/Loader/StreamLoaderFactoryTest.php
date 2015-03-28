<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection\Factory\Loader;

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory<extended>
 */
class StreamLoaderFactoryTest extends \Phpunit_Framework_TestCase
{
    public function testImplementsLoaderFactoryInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new StreamLoaderFactory();
    }

    public function testReturnExpectedName()
    {
        $loader = new StreamLoaderFactory();

        $this->assertEquals('stream', $loader->getName());
    }

    public function testCreateLoaderDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $loader = new StreamLoaderFactory();

        $loader->create($container, 'theLoaderName', array(
            'wrapper' => 'theWrapper',
            'context' => 'theContext',
        ));

        $this->assertTrue($container->hasDefinition('liip_imagine.binary.loader.theloadername'));

        $loaderDefinition = $container->getDefinition('liip_imagine.binary.loader.theloadername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $loaderDefinition);
        $this->assertEquals('liip_imagine.binary.loader.prototype.stream', $loaderDefinition->getParent());

        $this->assertEquals('theWrapper', $loaderDefinition->getArgument(0));
        $this->assertEquals('theContext', $loaderDefinition->getArgument(1));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "wrapper" at path "stream" must be configured.
     */
    public function testThrowIfWrapperNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stream', 'array');

        $resolver = new StreamLoaderFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, array());
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedWrapper = 'theWrapper';
        $expectedContext = 'theContext';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stream', 'array');

        $loader = new StreamLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'stream' => array(
                'wrapper' => $expectedWrapper,
                'context' => $expectedContext,
            ),
        ));

        $this->assertArrayHasKey('wrapper', $config);
        $this->assertEquals($expectedWrapper, $config['wrapper']);

        $this->assertArrayHasKey('context', $config);
        $this->assertEquals($expectedContext, $config['context']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stream', 'array');

        $loader = new StreamLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, array(
            'stream' => array(
                'wrapper' => 'aWrapper',
            ),
        ));

        $this->assertArrayHasKey('context', $config);
        $this->assertNull($config['context']);
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

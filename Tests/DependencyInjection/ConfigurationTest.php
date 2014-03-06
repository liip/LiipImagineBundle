<?php
namespace Liip\ImagineBundle\Tests\DependencyInjection;

use Liip\ImagineBundle\DependencyInjection\Configuration;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurationTest extends \Phpunit_Framework_TestCase
{
    public function testImplementsConfigurationInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Configuration');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\Config\Definition\ConfigurationInterface'));
    }

    public function testCouldBeConstructedWithResolversAndLoadersFactoriesAsArguments()
    {
        new Configuration(array(), array());
    }

    public function testInjectLoaderFactoryConfig()
    {
        $config = $this->processConfiguration(
            new Configuration(array(), array(new FooLoaderFactory)),
            array(array(
                'loaders' => array(
                    'aLoader' => array(
                        'foo' => array(
                            'foo_option' => 'theValue'
                        )
                    )
                )

            ))
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('aLoader', $config['loaders']);
        $this->assertArrayHasKey('foo', $config['loaders']['aLoader']);
        $this->assertArrayHasKey('foo_option', $config['loaders']['aLoader']['foo']);
        $this->assertEquals('theValue', $config['loaders']['aLoader']['foo']['foo_option']);
    }

    public function testAllowToUseLoaderFactorySeveralTimes()
    {
        $config = $this->processConfiguration(
            new Configuration(array(), array(new FooLoaderFactory)),
            array(array(
                'loaders' => array(
                    'aLoader' => array(
                        'foo' => array(
                            'foo_option' => 'theValue'
                        )
                    ),
                    'anotherLoader' => array(
                        'foo' => array(
                            'foo_option' => 'theValue'
                        )
                    )
                )

            ))
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('aLoader', $config['loaders']);
        $this->assertArrayHasKey('anotherLoader', $config['loaders']);
    }

    public function testInjectResolverFactoryConfig()
    {
        $config = $this->processConfiguration(
            new Configuration(array(new BarResolverFactory), array()),
            array(array(
                'resolvers' => array(
                    'aResolver' => array(
                        'bar' => array(
                            'bar_option' => 'theValue'
                        )
                    )
                )

            ))
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('aResolver', $config['resolvers']);
        $this->assertArrayHasKey('bar', $config['resolvers']['aResolver']);
        $this->assertArrayHasKey('bar_option', $config['resolvers']['aResolver']['bar']);
        $this->assertEquals('theValue', $config['resolvers']['aResolver']['bar']['bar_option']);
    }

    public function testAllowToUseResolverFactorySeveralTimes()
    {
        $config = $this->processConfiguration(
            new Configuration(array(new BarResolverFactory), array()),
            array(array(
                'resolvers' => array(
                    'aResolver' => array(
                        'bar' => array(
                            'bar_option' => 'theValue'
                        )
                    ),
                    'anotherResolver' => array(
                        'bar' => array(
                            'bar_option' => 'theValue'
                        )
                    ),
                )

            ))
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('aResolver', $config['resolvers']);
        $this->assertArrayHasKey('anotherResolver', $config['resolvers']);
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param array $configs
     *
     * @return array
     */
    protected function processConfiguration(ConfigurationInterface $configuration, array $configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}

class FooLoaderFactory implements LoaderFactoryInterface
{
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
    }

    public function getName()
    {
        return 'foo';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('foo_option')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}

class BarResolverFactory implements ResolverFactoryInterface
{
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
    }

    public function getName()
    {
        return 'bar';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('bar_option')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
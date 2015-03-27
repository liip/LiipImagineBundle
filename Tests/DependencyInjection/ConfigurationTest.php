<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection;

use Liip\ImagineBundle\DependencyInjection\Configuration;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Configuration
 */
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
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FooLoaderFactory(),
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'loaders' => array(
                    'aLoader' => array(
                        'foo' => array(
                            'foo_option' => 'theValue',
                        ),
                    ),
                ),

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
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FooLoaderFactory(),
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'loaders' => array(
                    'aLoader' => array(
                        'foo' => array(
                            'foo_option' => 'theValue',
                        ),
                    ),
                    'anotherLoader' => array(
                        'foo' => array(
                            'foo_option' => 'theValue',
                        ),
                    ),
                ),

            ))
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('aLoader', $config['loaders']);
        $this->assertArrayHasKey('anotherLoader', $config['loaders']);
    }

    public function testSetFilesystemLoaderAsDefaultLoaderIfNotDefined()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'loaders' => array(
                ),
            ))
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('filesystem', $config['loaders']['default']);
    }

    public function testSetFilesystemLoaderAsDefaultLoaderIfNull()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'loaders' => null,
            ))
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('filesystem', $config['loaders']['default']);
    }

    public function testThrowIfLoadersNotArray()
    {
        $this->setExpectedException('LogicException', 'Loaders has to be array');
        $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'loaders' => 'not_array',
            ))
        );
    }

    public function testSetFilesystemLoaderAsDefaultIfLoadersSectionNotDefined()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array())
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('filesystem', $config['loaders']['default']);
    }

    public function testSetWebPathResolversAsDefaultIfResolversSectionNotDefined()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array())
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testShouldNotOverwriteDefaultLoaderIfDefined()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ),
                array(
                    new FooLoaderFactory(),
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'loaders' => array(
                    'default' => array(
                        'foo' => array(
                            'foo_option' => 'theValue',
                        ),
                    ),
                ),

            ))
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('foo', $config['loaders']['default']);
    }

    public function testInjectResolverFactoryConfig()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ), array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'resolvers' => array(
                    'aResolver' => array(
                        'bar' => array(
                            'bar_option' => 'theValue',
                        ),
                    ),
                ),

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
            new Configuration(
                array(
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'resolvers' => array(
                    'aResolver' => array(
                        'bar' => array(
                            'bar_option' => 'theValue',
                        ),
                    ),
                    'anotherResolver' => array(
                        'bar' => array(
                            'bar_option' => 'theValue',
                        ),
                    ),
                ),

            ))
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('aResolver', $config['resolvers']);
        $this->assertArrayHasKey('anotherResolver', $config['resolvers']);
    }

    public function testSetWebPathAsDefaultResolverIfNotDefined()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ), array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'resolvers' => array(
                ),
            ))
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testSetWebPathAsDefaultResolverIfNull()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ), array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'resolvers' => null,
            ))
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testThrowsIfResolversNotArray()
    {
        $this->setExpectedException('LogicException', 'Resolvers has to be array');
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new WebPathResolverFactory(),
                ), array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'resolvers' => 'not_array',
            ))
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testShouldNotOverwriteDefaultResolverIfDefined()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'resolvers' => array(
                    'default' => array(
                        'bar' => array(
                            'bar_option' => 'theValue',
                        ),
                    ),
                ),

            ))
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('bar', $config['resolvers']['default']);
    }

    public function testNewFilterQualitySettings()
    {
        $config = $this->processConfiguration(
            new Configuration(
                array(
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ),
                array(
                    new FileSystemLoaderFactory(),
                )
            ),
            array(array(
                'filter_sets' => array(
                    'test' => array(
                        'jpeg_quality' => 70,
                        'png_compression_level' => 9,
                        'png_compression_filter' => PNG_ALL_FILTERS,
                    ),
                ),
            ))
        );

        $this->assertArrayHasKey('filter_sets', $config);
        $this->assertArrayHasKey('test', $config['filter_sets']);
        $this->assertArrayHasKey('jpeg_quality', $config['filter_sets']['test']);
        $this->assertEquals(70, $config['filter_sets']['test']['jpeg_quality']);
        $this->assertArrayHasKey('png_compression_level', $config['filter_sets']['test']);
        $this->assertEquals(9, $config['filter_sets']['test']['png_compression_level']);
        $this->assertArrayHasKey('png_compression_filter', $config['filter_sets']['test']);
        $this->assertEquals(PNG_ALL_FILTERS, $config['filter_sets']['test']['png_compression_filter']);
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param array                  $configs
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

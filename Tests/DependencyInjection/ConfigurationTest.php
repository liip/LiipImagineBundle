<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection;

use Liip\ImagineBundle\DependencyInjection\Configuration;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    public function testImplementsConfigurationInterface(): void
    {
        $rc = new \ReflectionClass(Configuration::class);

        $this->assertTrue($rc->implementsInterface(ConfigurationInterface::class));
    }

    public function testTemplatingSupportIsEnabledByDefault(): void
    {
        $config = $this->processConfiguration(new Configuration([], []), []);

        $this->assertTrue($config['templating']);
    }

    public function testCouldBeConstructedWithResolversAndLoadersFactoriesAsArguments(): void
    {
        $config = new Configuration([], []);

        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testInjectLoaderFactoryConfig(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FooLoaderFactory(),
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'loaders' => [
                    'aLoader' => [
                        'foo' => [
                            'foo_option' => 'theValue',
                        ],
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('aLoader', $config['loaders']);
        $this->assertArrayHasKey('foo', $config['loaders']['aLoader']);
        $this->assertArrayHasKey('foo_option', $config['loaders']['aLoader']['foo']);
        $this->assertSame('theValue', $config['loaders']['aLoader']['foo']['foo_option']);
    }

    public function testAllowToUseLoaderFactorySeveralTimes(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FooLoaderFactory(),
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'loaders' => [
                    'aLoader' => [
                        'foo' => [
                            'foo_option' => 'theValue',
                        ],
                    ],
                    'anotherLoader' => [
                        'foo' => [
                            'foo_option' => 'theValue',
                        ],
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('aLoader', $config['loaders']);
        $this->assertArrayHasKey('anotherLoader', $config['loaders']);
    }

    public function testSetFilesystemLoaderAsDefaultLoaderIfNotDefined(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'loaders' => [
                ],
            ]]
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('filesystem', $config['loaders']['default']);
    }

    public function testSetFilesystemLoaderAsDefaultLoaderIfNull(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'loaders' => null,
            ]]
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('filesystem', $config['loaders']['default']);
    }

    public function testThrowIfLoadersNotArray(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Loaders has to be array');

        $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'loaders' => 'not_array',
            ]]
        );
    }

    public function testSetFilesystemLoaderAsDefaultIfLoadersSectionNotDefined(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[]]
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('filesystem', $config['loaders']['default']);
    }

    public function testSetWebPathResolversAsDefaultIfResolversSectionNotDefined(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[]]
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testShouldNotOverwriteDefaultLoaderIfDefined(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ],
                [
                    new FooLoaderFactory(),
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'loaders' => [
                    'default' => [
                        'foo' => [
                            'foo_option' => 'theValue',
                        ],
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('loaders', $config);
        $this->assertArrayHasKey('default', $config['loaders']);
        $this->assertArrayHasKey('foo', $config['loaders']['default']);
    }

    public function testInjectResolverFactoryConfig(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ], [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'resolvers' => [
                    'aResolver' => [
                        'bar' => [
                            'bar_option' => 'theValue',
                        ],
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('aResolver', $config['resolvers']);
        $this->assertArrayHasKey('bar', $config['resolvers']['aResolver']);
        $this->assertArrayHasKey('bar_option', $config['resolvers']['aResolver']['bar']);
        $this->assertSame('theValue', $config['resolvers']['aResolver']['bar']['bar_option']);
    }

    public function testAllowToUseResolverFactorySeveralTimes(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'resolvers' => [
                    'aResolver' => [
                        'bar' => [
                            'bar_option' => 'theValue',
                        ],
                    ],
                    'anotherResolver' => [
                        'bar' => [
                            'bar_option' => 'theValue',
                        ],
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('aResolver', $config['resolvers']);
        $this->assertArrayHasKey('anotherResolver', $config['resolvers']);
    }

    public function testSetWebPathAsDefaultResolverIfNotDefined(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ], [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'resolvers' => [
                ],
            ]]
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testSetWebPathAsDefaultResolverIfNull(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ], [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'resolvers' => null,
            ]]
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testThrowsIfResolversNotArray(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Resolvers has to be array');

        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ], [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'resolvers' => 'not_array',
            ]]
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('web_path', $config['resolvers']['default']);
    }

    public function testShouldNotOverwriteDefaultResolverIfDefined(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'resolvers' => [
                    'default' => [
                        'bar' => [
                            'bar_option' => 'theValue',
                        ],
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('resolvers', $config);
        $this->assertArrayHasKey('default', $config['resolvers']);
        $this->assertArrayHasKey('bar', $config['resolvers']['default']);
    }

    public function testNewFilterQualitySettings(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new BarResolverFactory(),
                    new WebPathResolverFactory(),
                ],
                [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'filter_sets' => [
                    'test' => [
                        'jpeg_quality' => 70,
                        'png_compression_level' => 9,
                        'png_compression_filter' => PNG_ALL_FILTERS,
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('filter_sets', $config);
        $this->assertArrayHasKey('test', $config['filter_sets']);
        $this->assertArrayHasKey('jpeg_quality', $config['filter_sets']['test']);
        $this->assertSame(70, $config['filter_sets']['test']['jpeg_quality']);
        $this->assertArrayHasKey('png_compression_level', $config['filter_sets']['test']);
        $this->assertSame(9, $config['filter_sets']['test']['png_compression_level']);
        $this->assertArrayHasKey('png_compression_filter', $config['filter_sets']['test']);
        $this->assertSame(PNG_ALL_FILTERS, $config['filter_sets']['test']['png_compression_filter']);
    }

    public function testWebpSection(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ], [
                    new FileSystemLoaderFactory(),
                ]
            ),
            []
        );

        $this->assertArrayHasKey('webp', $config);
        $this->assertArrayHasKey('generate', $config['webp']);
        $this->assertFalse($config['webp']['generate']);
        $this->assertArrayHasKey('quality', $config['webp']);
        $this->assertSame(100, $config['webp']['quality']);
        $this->assertArrayHasKey('cache', $config['webp']);
        $this->assertNull($config['webp']['cache']);
        $this->assertArrayHasKey('data_loader', $config['webp']);
        $this->assertNull($config['webp']['data_loader']);
        $this->assertArrayHasKey('post_processors', $config['webp']);
        $this->assertSame([], $config['webp']['post_processors']);
    }

    public function testWebpEnableGenerate(): void
    {
        $config = $this->processConfiguration(
            new Configuration(
                [
                    new WebPathResolverFactory(),
                ], [
                    new FileSystemLoaderFactory(),
                ]
            ),
            [[
                'webp' => [
                    'generate' => true,
                ],
            ]]
        );

        $this->assertArrayHasKey('webp', $config);
        $this->assertArrayHasKey('generate', $config['webp']);
        $this->assertTrue($config['webp']['generate']);
    }

    protected function processConfiguration(ConfigurationInterface $configuration, array $configs): array
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

    public function getName(): string
    {
        return 'foo';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('foo_option')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }
}

class BarResolverFactory implements ResolverFactoryInterface
{
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
    }

    public function getName(): string
    {
        return 'bar';
    }

    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('bar_option')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }
}

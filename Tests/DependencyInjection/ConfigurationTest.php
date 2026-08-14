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

        $this->assertArrayHasKey('alternative_formats', $config);
        $this->assertArrayNotHasKey('webp', $config);
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

        $this->assertArrayNotHasKey('webp', $config);
        $this->assertArrayHasKey('alternative_formats', $config);
        $this->assertArrayHasKey('webp', $config['alternative_formats']);
        $this->assertTrue($config['alternative_formats']['webp']['generate']);
    }

    public function testAlternativeFormatsSection(): void
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
                'alternative_formats' => [
                    'webp' => [
                        'generate' => true,
                        'quality' => 80,
                    ],
                    'avif' => [
                        'generate' => false,
                        'mime_types' => ['image/avif'],
                        'priority' => 10,
                    ],
                ],
            ]]
        );

        $this->assertArrayHasKey('alternative_formats', $config);
        $this->assertArrayHasKey('webp', $config['alternative_formats']);
        $this->assertTrue($config['alternative_formats']['webp']['generate']);
        $this->assertSame(80, $config['alternative_formats']['webp']['quality']);

        $this->assertArrayHasKey('avif', $config['alternative_formats']);
        $this->assertFalse($config['alternative_formats']['avif']['generate']);
        $this->assertSame(['image/avif'], $config['alternative_formats']['avif']['mime_types']);
        $this->assertSame(10, $config['alternative_formats']['avif']['priority']);
    }

    public function testWebpNormalization(): void
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
                    'quality' => 90,
                    'post_processors' => [
                        'jpegoptim' => ['strip_all' => true],
                    ],
                ],
            ]]
        );

        $this->assertArrayNotHasKey('webp', $config);
        $this->assertArrayHasKey('alternative_formats', $config);
        $this->assertArrayHasKey('webp', $config['alternative_formats']);
        $this->assertTrue($config['alternative_formats']['webp']['generate']);
        $this->assertSame(90, $config['alternative_formats']['webp']['quality']);
        $this->assertArrayHasKey('jpegoptim', $config['alternative_formats']['webp']['post_processors']);
    }

    public function testAlternativeFormatsMimeTypesDefaultNormalization(): void
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
                'alternative_formats' => [
                    'webp' => [
                        'generate' => true,
                    ],
                    'avif' => [
                        'generate' => true,
                    ],
                ],
            ]]
        );

        $this->assertSame(['image/webp'], $config['alternative_formats']['webp']['mime_types']);
        $this->assertSame(['image/avif'], $config['alternative_formats']['avif']['mime_types']);
    }

    protected function processConfiguration(ConfigurationInterface $configuration, array $configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}

class FooLoaderFactory implements LoaderFactoryInterface
{
    public function create(ContainerBuilder $container, $loaderName, array $config): void
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
    public function create(ContainerBuilder $container, $resolverName, array $config): void
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

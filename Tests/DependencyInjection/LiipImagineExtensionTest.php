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

use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Yaml\Parser;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Configuration
 * @covers \Liip\ImagineBundle\DependencyInjection\LiipImagineExtension
 */
class LiipImagineExtensionTest extends AbstractTest
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $containerBuilder;

    public function testUserLoadThrowsExceptionUnlessDriverIsValid()
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $loader = new LiipImagineExtension();
        $loader->load([['driver' => 'foo']], new ContainerBuilder());
    }

    public function testLoadFilterSetsDefaults()
    {
        $this->createConfigurationWithOneEmptyFilterSet();
        $filterSets = $this->containerBuilder->getParameter('liip_imagine.filter_sets');

        $this->assertCount(1, $filterSets);
        $this->assertArrayHasKey('empty_filter_set', $filterSets);

        $emptyFilterSet = $filterSets['empty_filter_set'];

        $this->assertSame(100, $emptyFilterSet['quality']);
        $this->assertNull($emptyFilterSet['jpeg_quality']);
        $this->assertNull($emptyFilterSet['png_compression_level']);
        $this->assertNull($emptyFilterSet['png_compression_filter']);
        $this->assertNull($emptyFilterSet['format']);
        $this->assertFalse($emptyFilterSet['animated']);
        $this->assertNull($emptyFilterSet['cache']);
        $this->assertNull($emptyFilterSet['data_loader']);
        $this->assertNull($emptyFilterSet['default_image']);
    }

    public function testLoadFilterSetsWithDefaults()
    {
        $this->createConfigurationWithDefaultsFilterSets();
        $filterSets = $this->containerBuilder->getParameter('liip_imagine.filter_sets');

        $this->assertCount(2, $filterSets);
        $this->assertArrayHasKey('filter_set_1', $filterSets);

        $filterSet1 = $filterSets['filter_set_1'];

        $this->assertSame(80, $filterSet1['jpeg_quality']);
        $this->assertSame(90, $filterSet1['quality']);
        $this->assertSame('my_new_loader', $filterSet1['data_loader']);
        $this->assertArrayHasKey('filters', $filterSet1);
        $this->assertCount(1, $filterSet1['filters']);
        $this->assertArrayHasKey('thumbnail', $filterSet1['filters']);
        $this->assertArrayHasKey('size', $filterSet1['filters']['thumbnail']);
        $this->assertSame([483, 350], $filterSet1['filters']['thumbnail']['size']);
        $this->assertArrayHasKey('mode', $filterSet1['filters']['thumbnail']);
        $this->assertSame('outbound', $filterSet1['filters']['thumbnail']['mode']);
        $this->assertArrayHasKey('post_processors', $filterSet1);
        $this->assertArrayHasKey('mozjpeg', $filterSet1['post_processors']);

        $filterSet2 = $filterSets['filter_set_2'];

        $this->assertSame(70, $filterSet2['jpeg_quality']);
        $this->assertSame(80, $filterSet2['quality']);
        $this->assertSame('my_loader', $filterSet2['data_loader']);
        $this->assertArrayHasKey('filters', $filterSet2);
        $this->assertCount(2, $filterSet2['filters']);

        $this->assertArrayHasKey('thumbnail', $filterSet2['filters']);
        $this->assertArrayHasKey('size', $filterSet2['filters']['thumbnail']);
        $this->assertSame([483, 350], $filterSet2['filters']['thumbnail']['size']);
        $this->assertArrayHasKey('mode', $filterSet2['filters']['thumbnail']);
        $this->assertSame('inset', $filterSet2['filters']['thumbnail']['mode']);

        $this->assertArrayHasKey('fixed', $filterSet2['filters']);
        $this->assertArrayHasKey('width', $filterSet2['filters']['fixed']);
        $this->assertSame(120, $filterSet2['filters']['fixed']['width']);

        $this->assertArrayHasKey('height', $filterSet2['filters']['fixed']);
        $this->assertSame(90, $filterSet2['filters']['fixed']['height']);

        $this->assertArrayHasKey('post_processors', $filterSet2);
        $this->assertArrayHasKey('mozjpeg', $filterSet2['post_processors']);
    }

    protected function createConfigurationWithDefaultsFilterSets()
    {
        if (!class_exists(Parser::class)) {
            $this->markTestSkipped('Requires the symfony/yaml package.');
        }

        $this->createConfiguration($this->getConfigurationWithDefaultsFilterSets());
    }

    protected function createConfigurationWithOneEmptyFilterSet()
    {
        if (!class_exists(Parser::class)) {
            $this->markTestSkipped('Requires the symfony/yaml package.');
        }

        $this->createConfiguration($this->getConfigurationWithOneEmptyFilterSet());
    }

    protected function getConfigurationWithOneEmptyFilterSet()
    {
        $yaml = <<<'EOF'
filter_sets:
    empty_filter_set: ~
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    protected function getConfigurationWithDefaultsFilterSets()
    {
        $yaml = <<<'EOF'
default_filter_set_settings:
    jpeg_quality: 70
    quality: 80
    data_loader: 'my_loader'
    filters:
        thumbnail: { mode: 'inset' }
    post_processors:
        mozjpeg: {}
filter_sets:
    filter_set_1:
        jpeg_quality: 80
        quality: 90
        data_loader: 'my_new_loader'
        filters:
            thumbnail: { size: [483, 350], mode: 'outbound' }
    filter_set_2:
        filters:
            thumbnail: { size: [483, 350] }
            fixed: { width: 120, height: 90 }
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    public function testLoadWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter('default', 'liip_imagine.cache.resolver.default');
        $this->assertAlias('liip_imagine.gd', 'liip_imagine');
        $this->assertHasDefinition('liip_imagine.controller');
        $this->assertDICConstructorArguments(
            $this->containerBuilder->getDefinition(ImagineController::class),
            [
                new Reference('liip_imagine.service.filter'),
                new Reference('liip_imagine.data.manager'),
                new Reference('liip_imagine.cache.signer'),
                new Reference('liip_imagine.controller.config'),
            ]
        );
    }

    public function testTemplatingFilterExtensionIsDeprecated()
    {
        $this->createEmptyConfiguration();

        $this->assertHasDefinition('liip_imagine.templating.filter_helper');
        $this->assertDefinitionIsDeprecated('liip_imagine.templating.filter_helper', 'The "liip_imagine.templating.filter_helper" service is deprecated since LiipImagineBundle 2.2 and will be removed in 3.0.');
    }

    public static function provideFactoryData()
    {
        return [
            [
                'liip_imagine.mime_type_guesser',
                [MimeTypeGuesser::class, 'getInstance'],
            ],
            [
                'liip_imagine.extension_guesser',
                [ExtensionGuesser::class, 'getInstance'],
            ],
        ];
    }

    /**
     * @dataProvider provideFactoryData
     *
     * @param string $service
     * @param string $factory
     */
    public function testFactoriesConfiguration($service, $factory)
    {
        $this->createEmptyConfiguration();
        $definition = $this->containerBuilder->getDefinition($service);

        $this->assertSame($factory, $definition->getFactory());
    }

    protected function createEmptyConfiguration(): void
    {
        $this->createConfiguration([]);
    }

    protected function createFullConfiguration(): void
    {
        $this->createConfiguration($this->getFullConfig());
    }

    /**
     * @group legacy
     * @expectedDeprecation Symfony templating integration has been deprecated since LiipImagineBundle 2.2 and will be removed in 3.0. Use Twig and use "false" as "liip_imagine.templating" value instead.
     */
    public function testHelperIsRegisteredWhenTemplatingIsEnabled()
    {
        $this->createConfiguration([
            'templating' => true,
        ]);
        $this->assertHasDefinition('liip_imagine.templating.filter_helper');
    }

    public function testHelperIsNotRegisteredWhenTemplatingIsDisabled()
    {
        $this->createConfiguration([
            'templating' => false,
        ]);
        $this->assertHasNotDefinition('liip_imagine.templating.filter_helper');
    }

    protected function createConfiguration(array $configuration): void
    {
        $this->containerBuilder = new ContainerBuilder();
        $loader = new LiipImagineExtension();
        $loader->addLoaderFactory(new FileSystemLoaderFactory());
        $loader->addResolverFactory(new WebPathResolverFactory());
        $loader->load([$configuration], $this->containerBuilder);

        $this->assertInstanceOf(ContainerBuilder::class, $this->containerBuilder);
    }

    protected function getFullConfig()
    {
        $yaml = <<<'EOF'
driver: imagick
cache: false
filter_sets:
    small:
        filters:
            thumbnail: { size: [100, ~], mode: inset }
        quality: 80
    medium_small_cropped:
        filters:
            thumbnail: { size: [223, 173], mode: outbound }
    medium_cropped:
        filters:
            thumbnail: { size: [232, 180], mode: outbound }
    medium:
        filters:
            thumbnail: { size: [232, 180], mode: inset }
    large_cropped:
        filters:
            thumbnail: { size: [483, 350], mode: outbound }
    large:
        filters:
            thumbnail: { size: [483, ~], mode: inset }
    xxl:
        filters:
            thumbnail: { size: [660, ~], mode: inset }
        quality: 100
    '':
        quality: 100
data_loader: my_loader
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param string $value
     * @param string $key
     */
    private function assertAlias($value, $key)
    {
        $this->assertSame($value, (string) $this->containerBuilder->getAlias($key), sprintf('%s alias is correct', $key));
    }

    /**
     * @param string $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertSame($value, $this->containerBuilder->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id)
    {
        $this->assertTrue(($this->containerBuilder->hasDefinition($id) ?: $this->containerBuilder->hasAlias($id)));
    }

    /**
     * @param string $id
     */
    private function assertHasNotDefinition($id)
    {
        $this->assertFalse(($this->containerBuilder->hasDefinition($id) || $this->containerBuilder->hasAlias($id)));
    }

    /**
     * @param Definition $definition
     * @param array      $arguments
     */
    private function assertDICConstructorArguments(Definition $definition, array $arguments)
    {
        $castArrayElementsToString = function (array $a): array {
            return array_map(function ($v) { return (string) $v; }, $a);
        };

        $implodeArrayElements = function (array $a): string {
            return sprintf('[%s]:%d', implode(',', $a), \count($a));
        };

        $expectedArguments = $castArrayElementsToString($arguments);
        $providedArguments = $castArrayElementsToString($definition->getArguments());

        $this->assertSame($expectedArguments, $providedArguments, vsprintf('Definition arguments (%s) do not match expected arguments (%s).', [
            $implodeArrayElements($providedArguments),
            $implodeArrayElements($expectedArguments),
        ]));
    }

    private function assertDefinitionIsDeprecated(string $id, string $message)
    {
        $definition = $this->containerBuilder->getDefinition($id);

        $this->assertTrue($definition->isDeprecated());
        $this->assertSame($message, $definition->getDeprecationMessage($id));
    }
}

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
            ]
        );
    }

    public function testCustomRouteRequirements()
    {
        $this->createFullConfiguration();
        $param = $this->containerBuilder->getParameter('liip_imagine.filter_sets');

        $this->assertTrue(isset($param['small']['filters']['route']['requirements']));

        $variable1 = $param['small']['filters']['route']['requirements']['variable1'];
        $this->assertSame('value1', $variable1, sprintf('%s parameter is correct', $variable1));
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

    protected function createEmptyConfiguration()
    {
        $this->containerBuilder = new ContainerBuilder();
        $loader = new LiipImagineExtension();
        $loader->addLoaderFactory(new FileSystemLoaderFactory());
        $loader->addResolverFactory(new WebPathResolverFactory());
        $loader->load([[]], $this->containerBuilder);

        $this->assertInstanceOf(ContainerBuilder::class, $this->containerBuilder);
    }

    protected function createFullConfiguration()
    {
        if (!class_exists(Parser::class)) {
            $this->markTestSkipped('Requires the symfony/yaml package.');
        }

        $this->containerBuilder = new ContainerBuilder();
        $loader = new LiipImagineExtension();
        $loader->addLoaderFactory(new FileSystemLoaderFactory());
        $loader->addResolverFactory(new WebPathResolverFactory());
        $loader->load([$this->getFullConfig()], $this->containerBuilder);

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
            route:
                requirements: { variable1: 'value1' }
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
}

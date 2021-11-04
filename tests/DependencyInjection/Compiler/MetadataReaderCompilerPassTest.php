<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass
 */
class MetadataReaderCompilerPassTest extends TestCase
{
    public function testProcessBasedOnExtensionsInEnvironment(): void
    {
        [$metadataServiceId, $metadataExifClass, $metadataDefaultClass] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition($metadataExifClass));

        $pass = new MetadataReaderCompilerPass();
        $pass->process($container);
        $this->assertInstanceOf(\extension_loaded('exif') ? $metadataExifClass : $metadataDefaultClass, $container->get($metadataServiceId));
    }

    public function testProcessWithoutExtExifAddsDefaultReader(): void
    {
        [$metadataServiceId, $metadataExifClass, $metadataDefaultClass] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition($metadataExifClass));

        $pass = $this->getMetadataReaderCompilerPass(false);

        $pass->process($container);
        $this->assertInstanceOf($metadataDefaultClass, $container->get($metadataServiceId));
    }

    public function testProcessWithExtExifKeepsExifReader(): void
    {
        [$metadataServiceId, $metadataExifClass] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition($metadataExifClass));

        $pass = static::getMetadataReaderCompilerPass(true);

        $pass->process($container);
        $this->assertInstanceOf($metadataExifClass, $container->get($metadataServiceId));
    }

    public function testDoesNotOverrideCustomReaderWhenExifNotAvailable(): void
    {
        [$metadataServiceId] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition('stdClass'));

        $pass = static::getMetadataReaderCompilerPass(false);

        $pass->process($container);
        $this->assertInstanceOf('stdClass', $container->get($metadataServiceId));
    }

    private static function getVisibilityRestrictedStaticProperty(\ReflectionClass $r, string $p): string
    {
        $property = $r->getProperty($p);
        $property->setAccessible(true);

        return $property->getValue();
    }

    /**
     * @throws \ReflectionException
     *
     * @return mixed[]
     */
    private static function getReaderParamAndDefaultAndExifValues(): array
    {
        $r = new \ReflectionClass(MetadataReaderCompilerPass::class);

        return [
            static::getVisibilityRestrictedStaticProperty($r, 'metadataReaderServiceId'),
            static::getVisibilityRestrictedStaticProperty($r, 'metadataReaderExifClass'),
            static::getVisibilityRestrictedStaticProperty($r, 'metadataReaderDefaultClass'),
        ];
    }

    /**
     * @return MockObject|MetadataReaderCompilerPass
     */
    private function getMetadataReaderCompilerPass(bool $isExifExtensionLoaded)
    {
        $mock = $this->getMockBuilder(MetadataReaderCompilerPass::class)
            ->setMethods(['isExifExtensionLoaded'])
            ->getMock();

        $mock
            ->expects($this->atLeastOnce())
            ->method('isExifExtensionLoaded')
            ->willReturn($isExifExtensionLoaded);

        return $mock;
    }
}

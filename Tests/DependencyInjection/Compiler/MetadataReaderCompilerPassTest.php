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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass
 */
class MetadataReaderCompilerPassTest extends TestCase
{
    public function testProcessBasedOnExtensionsInEnvironment()
    {
        [$metadataServiceId, $metadataExifClass, $metadataDefaultClass] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition($metadataExifClass));

        $pass = new MetadataReaderCompilerPass();
        $pass->process($container);
        $this->assertInstanceOf(\extension_loaded('exif') ? $metadataExifClass : $metadataDefaultClass, $container->get($metadataServiceId));
    }

    public function testProcessWithoutExtExifAddsDefaultReader()
    {
        [$metadataServiceId, $metadataExifClass, $metadataDefaultClass] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition($metadataExifClass));

        $pass = $this->getMetadataReaderCompilerPass(false);

        $pass->process($container);
        $this->assertInstanceOf($metadataDefaultClass, $container->get($metadataServiceId));
    }

    public function testProcessWithExtExifKeepsExifReader()
    {
        [$metadataServiceId, $metadataExifClass] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition($metadataExifClass));

        $pass = static::getMetadataReaderCompilerPass(true);

        $pass->process($container);
        $this->assertInstanceOf($metadataExifClass, $container->get($metadataServiceId));
    }

    public function testDoesNotOverrideCustomReaderWhenExifNotAvailable()
    {
        [$metadataServiceId] = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setDefinition($metadataServiceId, new Definition('stdClass'));

        $pass = static::getMetadataReaderCompilerPass(false);

        $pass->process($container);
        $this->assertInstanceOf('stdClass', $container->get($metadataServiceId));
    }

    /**
     * @param \ReflectionClass $r
     * @param string           $p
     *
     * @return string
     */
    private static function getVisibilityRestrictedStaticProperty(\ReflectionClass $r, $p)
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
    private static function getReaderParamAndDefaultAndExifValues()
    {
        $r = new \ReflectionClass(MetadataReaderCompilerPass::class);

        return [
            static::getVisibilityRestrictedStaticProperty($r, 'metadataReaderServiceId'),
            static::getVisibilityRestrictedStaticProperty($r, 'metadataReaderExifClass'),
            static::getVisibilityRestrictedStaticProperty($r, 'metadataReaderDefaultClass'),
        ];
    }

    /**
     * @param bool $isExifExtensionLoaded
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|MetadataReaderCompilerPass
     */
    private function getMetadataReaderCompilerPass($isExifExtensionLoaded)
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

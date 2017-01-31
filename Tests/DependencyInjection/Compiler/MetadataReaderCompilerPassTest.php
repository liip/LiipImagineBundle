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
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass
 * @covers \Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass
 */
class MetadataReaderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param \ReflectionClass $r
     * @param string           $p
     *
     * @return string
     */
    private static function getPrivateStaticProperty(\ReflectionClass $r, $p)
    {
        $property = $r->getProperty($p);
        $property->setAccessible(true);

        return $property->getValue();
    }

    /**
     * @return mixed[]
     */
    private static function getReaderParamAndDefaultAndExifValues()
    {
        $r = new \ReflectionClass('Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass');

        return array(
            static::getPrivateStaticProperty($r, 'metadataReaderParameter'),
            static::getPrivateStaticProperty($r, 'metadataReaderExifClass'),
            static::getPrivateStaticProperty($r, 'metadataReaderDefaultClass'),
        );
    }

    /**
     * @param bool $return
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|MetadataReaderCompilerPass
     */
    private function getMetadataReaderCompilerPass($return)
    {
        $mock = $this->getMockBuilder('Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass')
            ->setMethods(array('isExifExtensionLoaded'))
            ->getMock();

        $mock
            ->expects($this->atLeastOnce())
            ->method('isExifExtensionLoaded')
            ->willReturn($return);

        return $mock;
    }

    public function testProcessWithoutExtExifAddsDefaultReader()
    {
        list($metadataParameter, $metadataExifClass, $metadataDefaultClass) = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setParameter($metadataParameter, $metadataExifClass);

        $pass = $this->getMetadataReaderCompilerPass(false);
        $this->assertEquals($metadataExifClass, $container->getParameter($metadataParameter));

        $pass->process($container);
        $this->assertEquals($metadataDefaultClass, $container->getParameter($metadataParameter));
    }

    public function testProcessWithExtExifKeepsExifReader()
    {
        list($metadataParameter, $metadataExifClass) = static::getReaderParamAndDefaultAndExifValues();

        $container = new ContainerBuilder();
        $container->setParameter($metadataParameter, $metadataExifClass);

        $pass = static::getMetadataReaderCompilerPass(true);
        $this->assertEquals($metadataExifClass, $container->getParameter($metadataParameter));

        $pass->process($container);
        $this->assertEquals($metadataExifClass, $container->getParameter($metadataParameter));
    }
}

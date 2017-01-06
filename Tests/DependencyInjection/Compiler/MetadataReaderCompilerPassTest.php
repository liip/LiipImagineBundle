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
 * @covers Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass
 */
class MetadataReaderCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessWithoutExtExifAddsDefaultReader()
    {
        $container = new ContainerBuilder();
        $container->setParameter(
            MetadataReaderCompilerPass::METADATA_READER_PARAM,
            MetadataReaderCompilerPass::EXIF_METADATA_READER_CLASS
        );

        /** @var MetadataReaderCompilerPass $pass */
        $pass = $this->getMock(
            'Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass',
            array('extExifIsAvailable'),
            array()
        );
        $pass->expects($this->once())
            ->method('extExifIsAvailable')
            ->willReturn(false);

        //guard
        $this->assertEquals(
            MetadataReaderCompilerPass::EXIF_METADATA_READER_CLASS,
            $container->getParameter(MetadataReaderCompilerPass::METADATA_READER_PARAM)
        );

        $pass->process($container);

        $this->assertEquals(
            MetadataReaderCompilerPass::DEFAULT_METADATA_READER_CLASS,
            $container->getParameter(MetadataReaderCompilerPass::METADATA_READER_PARAM)
        );
    }

    public function testProcessWithExtExifKeepsExifReader()
    {
        $container = new ContainerBuilder();
        $container->setParameter(
            MetadataReaderCompilerPass::METADATA_READER_PARAM,
            MetadataReaderCompilerPass::EXIF_METADATA_READER_CLASS
        );

        /** @var MetadataReaderCompilerPass $pass */
        $pass = $this->getMock(
            'Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass',
            array('extExifIsAvailable'),
            array()
        );
        $pass->expects($this->once())
            ->method('extExifIsAvailable')
            ->willReturn(true);

        //guard
        $this->assertEquals(
            MetadataReaderCompilerPass::EXIF_METADATA_READER_CLASS,
            $container->getParameter(MetadataReaderCompilerPass::METADATA_READER_PARAM)
        );

        $pass->process($container);

        $this->assertEquals(
            MetadataReaderCompilerPass::EXIF_METADATA_READER_CLASS,
            $container->getParameter(MetadataReaderCompilerPass::METADATA_READER_PARAM)
        );
    }
}

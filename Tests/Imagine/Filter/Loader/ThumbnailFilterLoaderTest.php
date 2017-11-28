<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Filter;

use Imagine\Image\Box;
use Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader
 *
 * @author Alex Wilson <a@ax.gy>
 */
class ThumbnailFilterLoaderTest extends AbstractTest
{
    /**
     * @var int
     */
    const DUMMY_IMAGE_WIDTH = 500;

    /**
     * @var int
     */
    const DUMMY_IMAGE_HEIGHT = 600;

    /**
     * @param int $width
     * @param int $height
     * @param Box $expected
     *
     * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader::load
     *
     * @dataProvider heightWidthProvider
     */
    public function testLoad($width, $height, $expected)
    {
        $loader = new ThumbnailFilterLoader();

        $mockImageSize = new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        );
        $image = $this->getImageInterfaceMock();
        $image->method('getSize')->willReturn($mockImageSize);
        $image->expects($this->once())
            ->method('thumbnail')
            ->with($expected)
            ->willReturn($image);

        $options = array();
        $options['size'] = array($width, $height);

        $result = $loader->load($image, $options);
    }

    /**
     * @returns array Array containing width/height pairs and an expected size.
     */
    public function heightWidthProvider()
    {
        return array(
            array(200, 129, new Box(200, 129)),
            array(50, 50, new Box(50, 50)),
            array(1, 30, new Box(1, 30)),
            array(null, 60, new Box(50, 60)),
            array(50, null, new Box(50, 60)),
        );
    }

    /**
     * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader::load
     */
    public function testLoadUpscale()
    {
        $loader = new ThumbnailFilterLoader();

        $mockImageSize = new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        );
        $image = $this->getImageInterfaceMock();
        $image->method('getSize')->willReturn($mockImageSize);

        $options = array();
        $options['size'] = array(1000, 1000);
        $image->expects($this->never())->method('thumbnail');

        $result = $loader->load($image, $options);
    }
}

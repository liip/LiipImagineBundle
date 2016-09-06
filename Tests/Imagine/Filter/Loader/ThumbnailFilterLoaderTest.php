<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;
use Imagine\Image\Palette\Grayscale;
use Imagine\Image\Box;

/**
 * Test cases for ThumbnailFilterLoader class.
 *
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader
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
     * @dataProvider heightWidthProvider
     */
    public function testLoad($width, $height, $expected)
    {
        $loader = new ThumbnailFilterLoader();

        $mockImageSize = new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        );
        $image = $this->getMockImage();
        $image->method('getSize')->willReturn($mockImageSize);
        $image->method('copy')->willReturn($image);
        $image->expects($this->once())
            ->method('thumbnail')
            ->with($expected)
            ->willReturn($image);

        $options = [];
        $options['size'] = [$width, $height];
        $options['allow_upscale'] = true;

        $result = $loader->load($image, $options);
    }

   /**
     * @returns array Array containing width/height pairs and an expected size.
     */
    public function heightWidthProvider()
    {
        return [
            [200, 129, new Box(200, 129)],
            [50, 50, new Box(50, 50)],
            [1, 30, new Box(1, 30)],
            [null, 60, new Box(50, 60)],
            [50, null, new Box(50, 60)],
            [1000, 1000, new Box(1000, 1000)]
        ];
    }
}

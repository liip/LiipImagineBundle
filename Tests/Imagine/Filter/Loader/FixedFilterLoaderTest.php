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
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\FixedFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\FixedFilterLoader
 */
class FixedFilterLoaderTest extends AbstractTest
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
     * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\FixedFilterLoader::load
     *
     * @dataProvider heightWidthProvider
     */
    public function testLoad($width, $height, $expected)
    {
        $loader = new FixedFilterLoader();

        $mockImageSize = new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        );

        if ($mockImageSize->getWidth() / $mockImageSize->getHeight() > $expected->getWidth() / $expected->getHeight()) {
            $resize = $mockImageSize->heighten($expected->getHeight());
        } else {
            $resize = $mockImageSize->widen($expected->getWidth());
        }
        $origin = new Point(
            floor(($resize->getWidth() - $expected->getWidth()) / 2),
            floor(($resize->getHeight() - $expected->getHeight()) / 2)
        );

        $image = $this->getImageInterfaceMock();
        $image->method('getSize')->willReturn($mockImageSize);
        $image->expects($this->once())
            ->method('resize')
            ->with($resize)
            ->willReturn($image);
        $image->expects($this->once())
            ->method('crop')
            ->with($origin, $expected)
            ->willReturn($image);

        $options = [];
        $options['width'] = $width;
        $options['height'] = $height;

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
            [1280, 720, new Box(1280, 720)],
        ];
    }
}

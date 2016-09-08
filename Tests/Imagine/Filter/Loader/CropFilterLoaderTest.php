<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;
use Imagine\Image\Box;
use Imagine\Image\Point;

/**
 * Test cases for CropFilterLoader class.
 *
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader
 */
class CropFilterLoaderTest extends AbstractTest
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
     *
     * @dataProvider cropDataProvider
     */
    public function testLoad($coordinates, $area)
    {
        $x = $coordinates[0];
        $y = $coordinates[1];

        $width = $area[0];
        $height = $area[1];

        $loader = new CropFilterLoader();

        $mockImageSize = new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        );
        $image = $this->getMockImage();
        $image->expects($this->once())
            ->method('crop')
            ->with(new Point($x, $y), new Box($width, $height))
            ->willReturn($image);

        $options = array();
        $options['start'] = $coordinates;
        $options['size'] = $area;

        $result = $loader->load($image, $options);
    }

    /**
     * @returns array Array containing coordinate and width/height pairs.
     */
    public function cropDataProvider()
    {
        return array(
            array(array(140, 130), array(200, 129)),
            array(array(30, 60), array(50, 50)),
            array(array(400, 500), array(1, 30)),
        );
    }
}

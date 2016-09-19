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
use Liip\ImagineBundle\Imagine\Filter\Loader\ScaleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * Test cases for ScaleFilterLoader class.
 *
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\ScaleFilterLoader
 *
 * @author Alex Wilson <a@ax.gy>
 */
class ScaleFilterLoaderTest extends AbstractTest
{
    /**
     * @var int
     */
    const DUMMY_IMAGE_WIDTH = 500;

    /**
     * @var int
     */
    const DUMMY_IMAGE_HEIGHT = 600;

    protected function getMockImage()
    {
        $mockImageSize = new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        );
        $mockImage = parent::getMockImage();
        $mockImage->method('getSize')->willReturn(new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        ));

        return $mockImage;
    }

    /**
     * @covers Liip\ImagineBundle\Imagine\Filter\Loader\ScaleFilterLoader::load
     */
    public function testItShouldPreserveRatio()
    {
        $loader = new ScaleFilterLoader();
        $image = $this->getMockImage();
        $image->expects($this->once())
            ->method('resize')
            ->with(new Box(
                self::DUMMY_IMAGE_WIDTH,
                self::DUMMY_IMAGE_HEIGHT
            ))
            ->willReturn($image);

        $result = $loader->load($image, array(
          'to' => 1.0,
        ));
    }

    /**
     * @param int[] $dimension
     * @param Box   $expected
     *
     * @covers Liip\ImagineBundle\Imagine\Filter\Loader\ScaleFilterLoader::load
     *
     * @dataProvider dimensionsDataProvider
     */
    public function testItShouldUseDimensions($dimensions, $expected)
    {
        $loader = new ScaleFilterLoader();

        $image = $this->getMockImage();
        $image->expects($this->once())
            ->method('resize')
            ->with($expected)
            ->willReturn($image);

        $options = array(
            'dim' => $dimensions,
        );

        $result = $loader->load($image, $options);
    }

    /**
     * @covers Liip\ImagineBundle\Imagine\Filter\Loader\ScaleFilterLoader::load
     *
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowInvalidArgumentException()
    {
        $scale = new ScaleFilterLoader('foo', 'bar');
        $scale->load($this->getMockImage(), array());
    }

    /**
     * @returns array Array containing coordinate and width/height pairs.
     */
    public function dimensionsDataProvider()
    {
        return array(
            array(array(150, 150), new Box(125, 150)),
            array(array(30, 60), new Box(30, 36)),
            array(array(1000, 1200), new Box(1000, 1200)),
        );
    }
}

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
use Liip\ImagineBundle\Imagine\Filter\Loader\UpscaleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * Test cases for UpScaleFilterLoader class.
 *
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\UpScaleFilterLoader
 *
 * @author Alex Wilson <a@ax.gy>
 */
class UpscaleFilterLoaderTest extends AbstractTest
{
    /**
     * @var int
     */
    const DUMMY_IMAGE_WIDTH = 600;

    /**
     * @var int
     */
    const DUMMY_IMAGE_HEIGHT = 400;

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
     * @dataProvider minScaleDataProvider
     */
    public function testShouldScale($dimensions, $expected)
    {
        $loader = new UpscaleFilterLoader();
        $image = $this->getMockImage();
        $image->expects($this->once())
            ->method('resize')
            ->with($expected)
            ->willReturn($image);

        $options = array(
            'min' => $dimensions,
        );

        $result = $loader->load($image, $options);
    }

    /**
     * @returns array Array containing coordinate and width/height pairs.
     */
    public function minScaleDataProvider()
    {
        return array(
            array(array(1000, 600), new Box(1000, 667)),
            array(array(1200, 300), new Box(1200, 800)),
        );
    }

    /**
     * @dataProvider minNotScaleDataProvider
     */
    public function testShouldNotScale($dimensions, $expected)
    {
        $loader = new UpscaleFilterLoader();
        $image = $this->getMockImage();
        $image->expects($this->never())
            ->method('resize')
            ->with($expected)
            ->willReturn($image);

        $options = array(
            'min' => $dimensions,
        );

        $result = $loader->load($image, $options);
    }

    /**
     * @returns array Array containing coordinate and width/height pairs.
     */
    public function minNotScaleDataProvider()
    {
        return array(
            array(array(300, 200), new Box(600, 400)),
            array(array(600, 400), new Box(600, 400)),
        );
    }



}

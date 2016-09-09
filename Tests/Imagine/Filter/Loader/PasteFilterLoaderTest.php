<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\Loader\PasteFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;
use Imagine\Image\Box;
use Imagine\Image\Point;

/**
 * Test cases for PasteFilterLoader class.
 *
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\PasteFilterLoader
 *
 * @author Alex Wilson <a@ax.gy>
 */
class PasteFilterLoaderTest extends AbstractTest
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
     * @param int   $x
     * @param int   $y
     * @param Point $expected
     *
     * @covers Liip\ImagineBundle\Imagine\Filter\Loader\PasteFilterLoader::load
     *
     * @dataProvider pasteProvider
     */
    public function testLoad($x, $y, $expected)
    {
        $mockImageSize = new Box(
            self::DUMMY_IMAGE_WIDTH,
            self::DUMMY_IMAGE_HEIGHT
        );
        $image = $this->getMockImage();
        $image->method('getSize')->willReturn($mockImageSize);
        $image->method('copy')->willReturn($image);
        $image->expects($this->once())
            ->method('paste')
            ->with($image, $expected)
            ->willReturn($image);

        $imagineMock = $this->createImagineMock();
        $imagineMock
            ->method('open')
            ->willReturn($image);
        $loader = new PasteFilterLoader($imagineMock, '');

        $options = array();
        $options['start'] = array($x, $y);
        $options['image'] = '';

        $result = $loader->load($image, $options);
    }

    /**
     * @returns array Array containing coordinates to paste.
     */
    public function pasteProvider()
    {
        return array(
            array(200, 129, new Point(200, 129)),
            array(50, 50, new Point(50, 50)),
            array(1, 30, new Point(1, 30)),
        );
    }
}

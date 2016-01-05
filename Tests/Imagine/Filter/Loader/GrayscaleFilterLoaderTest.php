<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Imagine\Gd\Imagine;
use Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * Test cases for GrayscaleFilterLoader class.
 *
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader
 *
 * @author Gregoire Humeau <gregoire.humeau@gmail.com>
 */
class GrayscaleFilterLoaderTest extends AbstractTest
{
    public function testLoadGrayscale()
    {
        $loader = new GrayscaleFilterLoader();
        $palette = new RGB();
        $imagine = new Imagine();

        // Generate blue image
        $image = $imagine->create(new Box(20, 20), $palette->color(array(20, 90, 240)));

        //Apply Grayscale filter
        $result = $loader->load($image);

        //Test result
        $pixel = $result->getColorAt(new Point(10, 10));
        $this->assertEquals('#565656', (string) $pixel);
    }
}

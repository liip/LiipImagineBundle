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

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader
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
        $image = $imagine->create(new Box(20, 20), $palette->color([20, 90, 240]));

        //Apply Grayscale filter
        $result = $loader->load($image);

        //Test result
        $pixel = $result->getColorAt(new Point(10, 10));
        $this->assertSame('#565656', (string) $pixel);
    }
}

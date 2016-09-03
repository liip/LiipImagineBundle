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

use Liip\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader
 */
class InterlaceFilterLoaderTest extends AbstractTest
{
    public function testLoad()
    {
        $loader = new InterlaceFilterLoader();

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('interlace')
            ->with('TEST')
        ;

        $result = $loader->load($image, array('mode' => 'TEST'));

        $this->assertInstanceOf('Imagine\Image\ImageInterface', $result);
    }
}

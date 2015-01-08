<?php

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

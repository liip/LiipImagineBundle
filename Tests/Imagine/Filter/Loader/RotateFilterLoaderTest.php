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

use Liip\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader
 *
 * @author Bocharsky Victor <bocharsky.bw@gmail.com>
 */
class RotateFilterLoaderTest extends AbstractTest
{
    public function testLoadRotate0Degrees()
    {
        $loader = new RotateFilterLoader();

        $image = $this->getImageInterfaceMock();

        $result = $loader->load($image, ['angle' => 0]);
        $this->assertSame($image, $result);
    }

    public function testLoadRotate90Degrees()
    {
        $loader = new RotateFilterLoader();

        $image = $this->getImageInterfaceMock();
        $rotatedImage = $this->getImageInterfaceMock();

        $image
            ->expects($this->once())
            ->method('rotate')
            ->with(90)
            ->willReturn($rotatedImage);

        $result = $loader->load($image, ['angle' => 90]);
        $this->assertSame($rotatedImage, $result);
    }
}

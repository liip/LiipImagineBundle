<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Image\Filter\Loader;

use Imagine\Image\Box;
use Liip\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader
 *
 * @author Minin Anton <anton.a.minin@gmail.com>
 */
class DownscaleFilterLoaderTest extends AbstractTest
{
    /**
     * @dataProvider provideSizes
     */
    public function testDontScaleUp($initialSize, $resultSize)
    {
        $this->assertLessThanOrEqual($initialSize->getHeight(), $resultSize->getHeight());
        $this->assertLessThanOrEqual($initialSize->getWidth(), $resultSize->getWidth());
    }

    /**
     * @dataProvider provideSizes
     */
    public function testFitBoundingBox($initialSize, $resultSize)
    {
        $this->assertLessThanOrEqual(100, $resultSize->getHeight());
        $this->assertLessThanOrEqual(90, $resultSize->getWidth());
    }

    public function provideSizes()
    {
        $loader = new DownscaleFilterLoader();

        $initialSize = new Box(50, 200);
        $resultSize = new Box(50, 200);

        $image = $this->getImageInterfaceMock();
        $image
            ->method('getSize')
            ->willReturn($initialSize);
        $image
            ->method('resize')
            ->willReturnCallback(function ($box) use (&$resultSize) {
                $resultSize = $box;
            });

        $loader->load($image, array('max' => array(100, 90)));

        yield [$initialSize, $resultSize];
    }
}

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
use Imagine\Image\ImageInterface;
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
     *
     * @param Box $resultSize
     * @param Box $initialSize
     */
    public function testDontScaleUp($resultSize, $initialSize): void
    {
        $this->assertLessThanOrEqual($initialSize->getHeight(), $resultSize->getHeight());
        $this->assertLessThanOrEqual($initialSize->getWidth(), $resultSize->getWidth());
    }

    /**
     * @dataProvider provideSizes
     *
     * @param Box $resultSize
     */
    public function testFitBoundingBox($resultSize): void
    {
        $this->assertLessThanOrEqual(100, $resultSize->getHeight());
        $this->assertLessThanOrEqual(90, $resultSize->getWidth());
    }

    /**
     * @return \Generator|Box[]
     */
    public function provideSizes()
    {
        $loader = new DownscaleFilterLoader();

        $initialSize = new Box(50, 200);
        $resultSize = new Box(50, 200);

        $image = $this->createMock(ImageInterface::class);
        $image
            ->method('getSize')
            ->willReturn($initialSize);
        $image
            ->method('resize')
            ->willReturnCallback(function (Box $box) use ($image, &$resultSize): ImageInterface {
                $resultSize = $box;

                return $image;
            });

        $loader->load($image, ['max' => [100, 90]]);

        yield [$resultSize, $initialSize];
    }
}

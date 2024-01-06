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
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader
 *
 * @author Alex Wilson <a@ax.gy>
 */
class ResizeFilterLoaderTest extends AbstractTest
{
    /**
     * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader::load
     *
     * @dataProvider resizeDataProvider
     */
    public function testLoad(int $width, int $height): void
    {
        $loader = new ResizeFilterLoader();

        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
            ->method('resize')
            ->with(new Box($width, $height))
            ->willReturn($image);

        $options = [];
        $options['size'] = [$width, $height];

        $loader->load($image, $options);
    }

    public function resizeDataProvider(): array
    {
        return [
            [140, 130],
            [30, 60],
            [400, 500],
        ];
    }
}

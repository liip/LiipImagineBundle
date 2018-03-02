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
     * @param int $width
     * @param int $height
     *
     * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader::load
     *
     * @dataProvider resizeDataProvider
     */
    public function testLoad($width, $height)
    {
        $loader = new ResizeFilterLoader();

        $image = $this->getImageInterfaceMock();
        $image->expects($this->once())
            ->method('resize')
            ->with(new Box($width, $height))
            ->willReturn($image);

        $options = [];
        $options['size'] = [$width, $height];

        $loader->load($image, $options);
    }

    /**
     * @returns array Array containing width/height pairs.
     */
    public function resizeDataProvider()
    {
        return [
            [140, 130],
            [30, 60],
            [400, 500],
        ];
    }
}

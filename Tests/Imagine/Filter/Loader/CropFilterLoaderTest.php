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
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader
 *
 * @author Alex Wilson <a@ax.gy>
 */
class CropFilterLoaderTest extends AbstractTest
{
    /**
     * @param int[] $coordinates
     * @param int[] $area
     *
     * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader::load
     *
     * @dataProvider cropDataProvider
     */
    public function testLoad($coordinates, $area)
    {
        $x = $coordinates[0];
        $y = $coordinates[1];

        $width = $area[0];
        $height = $area[1];

        $loader = new CropFilterLoader();

        $image = $this->getImageInterfaceMock();
        $image->expects($this->once())
            ->method('crop')
            ->with(new Point($x, $y), new Box($width, $height))
            ->willReturn($image);

        $options = [];
        $options['start'] = $coordinates;
        $options['size'] = $area;

        $loader->load($image, $options);
    }

    /**
     * @returns array Array containing coordinate and width/height pairs.
     */
    public function cropDataProvider()
    {
        return [
            [[140, 130], [200, 129]],
            [[30, 60], [50, 50]],
            [[400, 500], [1, 30]],
        ];
    }
}

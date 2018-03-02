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
use Liip\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader
 *
 * Due to int casting in Imagine\Image\Box which can lead to wrong pixel
 * numbers ( e.g. float(201) casted to int(200) ). Solved by round the
 * floating number before passing to the Box constructor.
 */
class FloatToIntCastByRoundDownscaleFilterLoaderTest extends AbstractTest
{
    public function testLoad()
    {
        $loader = new DownscaleFilterLoader();
        $imagine = new Imagine();
        $image = $imagine->open(__DIR__.'/../../../Fixtures/assets/square-300x300.png');

        $options = [
            'max' => [201, 201],
        ];

        $image = $loader->load($image, $options);
        $size = $image->getSize();

        $this->assertSame($options['max'], [$size->getWidth(), $size->getHeight()]);
    }
}

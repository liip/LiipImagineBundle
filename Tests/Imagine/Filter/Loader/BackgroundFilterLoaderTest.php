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
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\BackgroundFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\BackgroundFilterLoader
 */
class BackgroundFilterLoaderTest extends AbstractTest
{
    /**
     * The option is handed straight to the palette as the alpha of the background color, so it
     * reads as an opacity: 100 keeps the background, 0 makes it disappear.
     *
     * @dataProvider provideTransparencyData
     */
    public function testTransparencyIsTheAlphaOfTheBackground(?int $transparency, int $expectedAlpha): void
    {
        $imagine = new Imagine();
        $loader = new BackgroundFilterLoader($imagine);

        $options = ['color' => '#ff0000', 'size' => [20, 20], 'position' => 'center'];

        if (null !== $transparency) {
            $options['transparency'] = $transparency;
        }

        $result = $loader->load($imagine->create(new Box(10, 10)), $options);

        // a corner of the canvas, outside of the pasted image
        $this->assertSame($expectedAlpha, $result->getColorAt(new Point(0, 0))->getAlpha());
    }

    /**
     * @return iterable<string, array{int|null, int}>
     */
    public static function provideTransparencyData(): iterable
    {
        yield 'no transparency given' => [null, 100];
        yield 'fully transparent' => [0, 0];
        yield 'half way' => [50, 50];
        yield 'opaque' => [100, 100];
    }
}

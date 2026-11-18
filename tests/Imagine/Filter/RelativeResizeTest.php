<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Filter;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\RelativeResize;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\RelativeResize
 */
class RelativeResizeTest extends TestCase
{
    public function testApplyReturnsResizedImage(): void
    {
        $image = (new Imagine())->create(new Box(200, 100));

        $result = (new RelativeResize('heighten', 50))->apply($image);

        $this->assertInstanceOf(ImageInterface::class, $result);
        $this->assertSame(50, $result->getSize()->getHeight());
        $this->assertSame(100, $result->getSize()->getWidth());
    }
}

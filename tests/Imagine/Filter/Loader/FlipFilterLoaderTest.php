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

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\FlipFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\FlipFilterLoader
 */
class FlipFilterLoaderTest extends AbstractTest
{
    public static function provideLoadWithAxisXOptionData(): array
    {
        return [
            ['x'],
            ['horizontal'],
        ];
    }

    /**
     * @dataProvider provideLoadWithAxisXOptionData
     */
    public function testLoadWithAxisXOption(string $axis): void
    {
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
            ->method('flipHorizontally')
            ->willReturn($image);

        $this->createFlipFilterLoaderInstance()->load($image, ['axis' => $axis]);
    }

    public static function provideLoadWithAxisYOptionData(): array
    {
        return [
            ['y'],
            ['vertical'],
        ];
    }

    /**
     * @dataProvider provideLoadWithAxisYOptionData
     */
    public function testLoadWithAxisYOption(string $axis): void
    {
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
            ->method('flipVertically')
            ->willReturn($image);

        $this->createFlipFilterLoaderInstance()->load($image, ['axis' => $axis]);
    }

    public function testThrowsOnInvalidOptions(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "axis" option must be set to "x", "horizontal", "y", or "vertical".');

        $loader = new FlipFilterLoader();
        $loader->load($this->createMock(ImageInterface::class), [
            'axis' => 'invalid',
        ]);
    }

    private function createFlipFilterLoaderInstance(): FlipFilterLoader
    {
        return new FlipFilterLoader();
    }
}

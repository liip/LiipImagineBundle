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
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Imagine\Filter\Loader\WatermarkFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\WatermarkFilterLoader
 */
class WatermarkFilterLoaderTest extends AbstractTest
{
    public function testOpensImageRelativeToProjectDir(): void
    {
        $watermark = $this->createMock(ImageInterface::class);
        $watermark->method('getSize')->willReturn(new Box(10, 10));

        $image = $this->createMock(ImageInterface::class);
        $image->method('getSize')->willReturn(new Box(100, 100));
        $image
            ->expects($this->once())
            ->method('paste')
            ->with($watermark, new Point(0, 0))
            ->willReturn($image);

        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->once())
            ->method('open')
            ->with('/project/assets/watermark.png')
            ->willReturn($watermark);

        $loader = new WatermarkFilterLoader($imagineMock, '/project');
        $loader->load($image, ['image' => 'assets/watermark.png', 'position' => 'topleft']);
    }

    /**
     * @return string[][]
     */
    public static function invalidPathProvider(): array
    {
        return [
            ['../outside/foobar.png'],
            ['assets/../../outside/foobar.png'],
            ['assets\\..\\..\\outside\\foobar.png'],
        ];
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testThrowsIfImageOutsideProjectDir(string $path): void
    {
        $imagineMock = $this->createMock(ImagineInterface::class);
        $imagineMock
            ->expects($this->never())
            ->method('open');
        $loader = new WatermarkFilterLoader($imagineMock, '/project');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not contain ".." segments');

        $loader->load($this->createMock(ImageInterface::class), ['image' => $path]);
    }
}

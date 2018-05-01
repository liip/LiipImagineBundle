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

use Liip\ImagineBundle\Imagine\Filter\Loader\FlipFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\FlipFilterLoader
 */
class FlipFilterLoaderTest extends AbstractTest
{
    /**
     * @return array
     */
    public static function provideLoadWithAxisXOptionData()
    {
        return [
            ['x'],
            ['horizontal'],
        ];
    }

    /**
     * @param string $axis
     *
     * @dataProvider provideLoadWithAxisXOptionData
     */
    public function testLoadWithAxisXOption($axis)
    {
        $image = $this->getImageInterfaceMock();
        $image->expects($this->once())
            ->method('flipHorizontally')
            ->willReturn($image);

        $this->createFlipFilterLoaderInstance()->load($image, ['axis' => $axis]);
    }

    /**
     * @return array
     */
    public static function provideLoadWithAxisYOptionData()
    {
        return [
            ['y'],
            ['vertical'],
        ];
    }

    /**
     * @param string $axis
     *
     * @dataProvider provideLoadWithAxisYOptionData
     */
    public function testLoadWithAxisYOption($axis)
    {
        $image = $this->getImageInterfaceMock();
        $image->expects($this->once())
            ->method('flipVertically')
            ->willReturn($image);

        $this->createFlipFilterLoaderInstance()->load($image, ['axis' => $axis]);
    }

    public function testThrowsOnInvalidOptions()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "axis" option must be set to "x", "horizontal", "y", or "vertical".');

        $loader = new FlipFilterLoader();
        $loader->load($this->getImageInterfaceMock(), [
            'axis' => 'invalid',
        ]);
    }

    /**
     * @return FlipFilterLoader
     */
    private function createFlipFilterLoaderInstance()
    {
        return new FlipFilterLoader();
    }
}

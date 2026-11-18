<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Helper;

use Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper
 */
class PathHelperTest extends TestCase
{
    /**
     * @return string[][]
     */
    public static function provideTraversalPaths(): array
    {
        return [
            ['..'],
            ['../file.jpg'],
            ['/../file.jpg'],
            ['dir/../file.jpg'],
            ['dir/../../file.jpg'],
            ['dir/..'],
            ['dir/../'],
            ['dir//../file.jpg'],
            ['..\\file.jpg'],
            ['dir\\..\\file.jpg'],
            ['dir\\..'],
            ['dir/..\\file.jpg'],
            ['rc/abcd1234/../../file.jpg'],
        ];
    }

    /**
     * @dataProvider provideTraversalPaths
     */
    public function testContainsTraversal(string $path): void
    {
        $this->assertTrue(PathHelper::containsTraversal($path));
    }

    /**
     * @return string[][]
     */
    public static function provideSafePaths(): array
    {
        return [
            [''],
            ['file.jpg'],
            ['/file.jpg'],
            ['dir/file.jpg'],
            ['./file.jpg'],
            ['dir/./file.jpg'],
            ['file..jpg'],
            ['..file.jpg'],
            ['file.jpg..'],
            ['dir../file.jpg'],
            ['...'],
            ['dir/.../file.jpg'],
            ['http---example.com/file.jpg'],
        ];
    }

    /**
     * @dataProvider provideSafePaths
     */
    public function testDoesNotContainTraversal(string $path): void
    {
        $this->assertFalse(PathHelper::containsTraversal($path));
    }
}

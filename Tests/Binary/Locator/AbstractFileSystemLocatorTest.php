<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Locator;

use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Binary\Locator\LocatorInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractFileSystemLocatorTest extends TestCase
{
    public function testImplementsLocatorInterface()
    {
        $this->assertInstanceOf(LocatorInterface::class, new FileSystemLocator());
    }

    public function testThrowsIfEmptyRootPath()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator('');
    }

    public function testThrowsIfRootPathDoesNotExist()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator('/a/bad/root/path');
    }

    public function testThrowsIfFileDoesNotExist()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable');

        $this->getFileSystemLocator(__DIR__)->locate('fileNotExist');
    }

    public function testThrowsIfRootPlaceholderInvalid()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('Invalid root placeholder "@invalid-placeholder" for path');

        $this->getFileSystemLocator(__DIR__)->locate('@invalid-placeholder:file.ext');
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
    {
        return [];
    }

    /**
     * @dataProvider provideLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testLoad($root, $path)
    {
        $this->assertStringStartsWith(realpath($root.'/../'), $this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public static function provideMultipleRootLoadCases()
    {
        return [];
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testMultipleRootLoadCases($root, $path)
    {
        $this->assertNotNull($this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public function provideOutsideRootPathsData()
    {
        return [
            ['../Loader/../../Binary/Loader/../../../Resources/config/routing.yaml'],
            ['../../Binary/'],
        ];
    }

    /**
     * @dataProvider provideOutsideRootPathsData
     *
     * @param string $path
     */
    public function testThrowsIfRealPathOutsideRootPath($path)
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator($path)->locate($path);
    }

    /**
     * @param string[]|string $paths
     *
     * @return LocatorInterface
     */
    abstract protected function getFileSystemLocator($paths);
}

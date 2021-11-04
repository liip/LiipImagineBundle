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
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

abstract class AbstractFileSystemLocatorTest extends TestCase
{
    public function testImplementsLocatorInterface(): void
    {
        $this->assertInstanceOf(LocatorInterface::class, new FileSystemLocator());
    }

    public function testThrowsIfEmptyRootPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator('');
    }

    public function testThrowsIfRootPathDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator('/a/bad/root/path');
    }

    public function testThrowsIfFileDoesNotExist(): void
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable');

        $this->getFileSystemLocator(__DIR__)->locate('fileNotExist');
    }

    public function testThrowsIfRootPlaceholderInvalid(): void
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Invalid root placeholder "@invalid-placeholder" for path');

        $this->getFileSystemLocator(__DIR__)->locate('@invalid-placeholder:file.ext');
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases(): array
    {
        return [];
    }

    /**
     * @dataProvider provideLoadCases
     */
    public function testLoad(string $root, string $path): void
    {
        $this->assertStringStartsWith(realpath($root.'/../'), $this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public static function provideMultipleRootLoadCases(): array
    {
        return [];
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     */
    public function testMultipleRootLoadCases(array $root, string $path): void
    {
        $this->assertNotNull($this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public function provideOutsideRootPathsData(): array
    {
        return [
            ['../Loader/../../Binary/Loader/../../../Resources/config/routing.yaml'],
            ['../../Binary/'],
        ];
    }

    /**
     * @dataProvider provideOutsideRootPathsData
     */
    public function testThrowsIfRealPathOutsideRootPath(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator($path)->locate($path);
    }

    /**
     * @param string[]|string $paths
     */
    abstract protected function getFileSystemLocator($paths): LocatorInterface;
}

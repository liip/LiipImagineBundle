<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Liip\ImagineBundle\Binary\Loader\ChainLoader;
use Liip\ImagineBundle\Binary\Loader\FileSystemLoader;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Binary\Locator\LocatorInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\FileBinary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Mime\MimeTypes;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\ChainLoader
 */
class ChainLoaderTest extends AbstractTest
{
    public function testImplementsLoaderInterface(): void
    {
        $this->assertInstanceOf(LoaderInterface::class, $this->getChainLoader());
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases(): array
    {
        $file = pathinfo(__FILE__, PATHINFO_BASENAME);

        return [
            [
                __DIR__,
                $file,
            ],
            [
                __DIR__.'/',
                $file,
            ],
            [
                __DIR__, '/'.
                $file,
            ],
            [
                __DIR__.'/../../Binary/Loader',
                '/'.$file,
            ],
            [
                realpath(__DIR__.'/..'),
                'Loader/'.$file,
            ],
            [
                __DIR__.'/../',
                '/Loader/../../Binary/Loader/'.$file,
            ],
        ];
    }

    /**
     * @dataProvider provideLoadCases
     */
    public function testLoad(string $root, string $path): void
    {
        $this->assertValidLoaderFindReturn($this->getChainLoader([$root])->find($path));
    }

    /**
     * @return array[]
     */
    public function provideInvalidPathsData(): array
    {
        return [
            ['../Loader/../../Binary/Loader/../../../Resources/config/routing.yaml'],
            ['../../Binary/'],
        ];
    }

    /**
     * @dataProvider provideInvalidPathsData
     */
    public function testThrowsIfFileDoesNotExist(string $path): void
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessageMatches('{Source image not resolvable "[^"]+" using "FileSystemLoader=\[foo\]" 1 loaders}');

        $this->getChainLoader()->find($path);
    }

    /**
     * @dataProvider provideInvalidPathsData
     */
    public function testThrowsIfFileDoesNotExistWithMultipleLoaders(string $path): void
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessageMatches('{Source image not resolvable "[^"]+" using "FileSystemLoader=\[foo\], FileSystemLoader=\[bar\]" 2 loaders \(internal exceptions: FileSystemLoader=\[.+\], FileSystemLoader=\[.+\]\)\.}');

        $this->getChainLoader([], [
            'foo' => $this->createFileSystemLoader(
                $this->getFileSystemLocator([
                    realpath(__DIR__.'/../../'),
                ])
            ),
            'bar' => $this->createFileSystemLoader(
                $this->getFileSystemLocator([
                    realpath(__DIR__.'/../../../'),
                ])
            ),
        ])->find($path);
    }

    /**
     * @param string[] $paths
     */
    private function getFileSystemLocator(array $paths = []): FileSystemLocator
    {
        return new FileSystemLocator($paths);
    }

    /**
     * @param string[]           $paths
     * @param FileSystemLoader[] $loaders
     */
    private function getChainLoader(array $paths = [], array $loaders = null): ChainLoader
    {
        if (null === $loaders) {
            $loaders = [
                'foo' => $this->createFileSystemLoader($this->getFileSystemLocator($paths ?: [__DIR__])),
            ];
        }

        return new ChainLoader($loaders);
    }

    private function assertValidLoaderFindReturn(FileBinary $return, string $message = ''): void
    {
        $this->assertInstanceOf(FileBinary::class, $return, $message);
        $this->assertStringStartsWith('text/', $return->getMimeType(), $message);
    }

    private function createFileSystemLoader(LocatorInterface $locator): FileSystemLoader
    {
        $mimeTypes = MimeTypes::getDefault();

        return new FileSystemLoader(
            $mimeTypes,
            $mimeTypes,
            $locator
        );
    }
}

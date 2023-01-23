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
use Liip\ImagineBundle\Exception\Binary\Loader\ChainAttemptNotLoadableException;
use Liip\ImagineBundle\Exception\Binary\Loader\ChainNotLoadableException;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\FileBinary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Mime\MimeTypes;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\ChainLoader
 * @covers \Liip\ImagineBundle\Exception\Binary\Loader\ChainAttemptNotLoadableException
 * @covers \Liip\ImagineBundle\Exception\Binary\Loader\ChainNotLoadableException
 */
class ChainLoaderTest extends AbstractTest
{
    public function testChainLoaderImplementsLoaderInterface(): void
    {
        $this->assertInstanceOf(LoaderInterface::class, self::instantiateChainLoader());
    }

    public function testChainAttemptNotLoadableExceptionImplementsNotLoadableException(): void
    {
        $this->assertInstanceOfNotLoadableException(
            self::instantiateChainAttemptNotLoadableException(),
            vsprintf('Expected "%s" to be an instance of "%s"', [
                ChainAttemptNotLoadableException::class,
                NotLoadableException::class,
            ])
        );
    }

    public function testChainNotLoadableExceptionImplementsNotLoadableException(): void
    {
        $this->assertInstanceOfNotLoadableException(
            self::instantiateChainNotLoadableException()
        );
    }

    private function assertInstanceOfNotLoadableException(object $provided, string $message = ''): void
    {
        $this->assertInstanceOf(NotLoadableException::class, $provided, $message
            ?? vsprintf('Expected class "%s" to be an instance of "%s"', [
                self::getReflectionObjectName($provided), NotLoadableException::class,
            ])
        );
    }

    public static function provideLoadCases(): \Generator
    {
        $file = pathinfo(__FILE__, PATHINFO_BASENAME);

        yield [__DIR__, $file];
        yield [__DIR__.'/', $file];
        yield [__DIR__, '/'.$file];
        yield [__DIR__.'/../../Binary/Loader', '/'.$file];
        yield [realpath(__DIR__.'/..'), 'Loader/'.$file];
        yield [__DIR__.'/../', '/Loader/../../Binary/Loader/'.$file];
    }

    /**
     * @dataProvider provideLoadCases
     */
    public function testLoad(string $root, string $path): void
    {
        $this->assertValidLoaderFindReturn(self::instantiateChainLoader([$root])->find($path), vsprintf(
            'Expected valid "%s::find()" return with root of "%s" and file path of "%s".', [
                ChainLoader::class,
                $root,
                $path,
            ]
        ));
    }

    public function provideInvalidPathsData(): \Generator
    {
        yield ['../Loader/../../Binary/Loader/../../../Resources/config/routing.yaml'];
        yield ['../../Binary/'];
    }

    /**
     * @dataProvider provideInvalidPathsData
     */
    public function testThrowsIfFileDoesNotExist(string $path): void
    {
        $this->expectException(NotLoadableException::class);
        $this->expectException(ChainNotLoadableException::class);
        $this->expectExceptionMessageMatchesBC('{Source image not resolvable "[^"]+" using "FileSystemLoader=\[foo\]" 1 loaders}');

        self::instantiateChainLoader()->find($path);
    }

    /**
     * @dataProvider provideInvalidPathsData
     */
    public function testThrowsIfFileDoesNotExistWithMultipleLoaders(string $path): void
    {
        $this->expectException(NotLoadableException::class);
        $this->expectException(ChainNotLoadableException::class);
        $this->expectExceptionMessageMatchesBC(
            '{Source image not resolvable "[^"]+" using "FileSystemLoader=\[foo\], '.
            'FileSystemLoader=\[bar\]" 2 loaders \(internal exceptions: FileSystemLoader=\[.+\], '.
            'FileSystemLoader=\[.+\]\)\.}'
        );

        self::instantiateChainLoader([], [
            'foo' => self::instantiateFileSystemLoader(
                self::instantiateFileSystemLocator([
                    realpath(__DIR__.'/../../'),
                ])
            ),
            'bar' => self::instantiateFileSystemLoader(
                self::instantiateFileSystemLocator([
                    realpath(__DIR__.'/../../../'),
                ])
            ),
        ])->find($path);
    }

    private function assertValidLoaderFindReturn(FileBinary $return, string $message = ''): void
    {
        $this->assertInstanceOf(FileBinary::class, $return, $message);
        $this->assertStringStartsWith('text/', $return->getMimeType(), $message);
    }

    private static function instantiateRandomlyPopulatedChainNotLoadableException(string $loaderPath = null, int $maxInternalExceptions = 10): ChainNotLoadableException
    {
        self::instantiateChainNotLoadableException($loaderPath, ...array_map(function (): ChainAttemptNotLoadableException {
            return self::instantiateChainAttemptNotLoadableException();
        }, range(0, self::generateRandomInteger(1, $maxInternalExceptions))));
    }

    private static function instantiateChainNotLoadableException(string $loaderPath = null, ChainAttemptNotLoadableException ...$exceptions): ChainNotLoadableException
    {
        return new ChainNotLoadableException($loaderPath ?? (__DIR__), ...$exceptions);
    }

    private static function instantiateChainAttemptNotLoadableException(): ChainAttemptNotLoadableException
    {
        return new ChainAttemptNotLoadableException(
            $name = sprintf('conf-item-%d', static::generateRandomInteger(1000, 9999)),
            self::instantiateFileSystemLoader(self::instantiateFileSystemLocator()),
            new NotLoadableException(sprintf('the "%s" loader encountered an error', $name))
        );
    }

    private static function instantiateFileSystemLoader(LocatorInterface $locator = null): FileSystemLoader
    {
        return new FileSystemLoader(
            $mime = MimeTypes::getDefault(), $mime, $locator ?? self::instantiateFileSystemLocator()
        );
    }

    /**
     * @param string[] $paths
     */
    private static function instantiateFileSystemLocator(array $paths = []): FileSystemLocator
    {
        return new FileSystemLocator($paths);
    }

    /**
     * @param string[]                        $paths
     * @param array<string, FileSystemLoader> $loaders
     */
    private static function instantiateChainLoader(array $paths = [], array $loaders = null): ChainLoader
    {
        return new ChainLoader($loaders ?? [
            'foo' => self::instantiateFileSystemLoader(
                self::instantiateFileSystemLocator($paths ?: [__DIR__])
            ),
        ]);
    }
}

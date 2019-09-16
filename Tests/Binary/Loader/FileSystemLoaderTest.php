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

use Liip\ImagineBundle\Binary\Loader\FileSystemLoader;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Binary\Locator\LocatorInterface;
use Liip\ImagineBundle\Model\FileBinary;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\FileSystemLoader
 */
class FileSystemLoaderTest extends TestCase
{
    public function testConstruction()
    {
        $loader = $this->getFileSystemLoader();

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    /**
     * @dataProvider provideMultipleWrongArgumentsConstructorCases
     *
     * @param $expectedMessage
     * @param $mimeGuesser
     * @param $extensionGuesser
     */
    public function testThrowsIfConstructedWithWrongTypeArguments($expectedMessage, $mimeGuesser, $extensionGuesser)
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        new FileSystemLoader(
            $mimeGuesser,
            $extensionGuesser,
            $this->getFileSystemLocator( $this->getDefaultDataRoots())
        );
    }

    /**
     * @return string[][]
     */
    public static function provideMultipleWrongArgumentsConstructorCases()
    {
        return [
            [
                '$mimeGuesser must be an instance of Symfony\Component\Mime\MimeTypeGuesserInterface or Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface',
                'foo',
                'bar'
            ],
            [
                '$extensionGuesser must be an instance of Symfony\Component\Mime\MimeTypesInterface or Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface',
                class_exists(MimeTypes::class) ? MimeTypes::getDefault() : MimeTypeGuesser::getInstance(),
                'bar'
            ],
        ];
    }

    public function testImplementsLoaderInterface()
    {
        $this->assertInstanceOf(LoaderInterface::class, $this->getFileSystemLoader());
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
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
     *
     * @param string $root
     * @param string $path
     */
    public function testLoad($root, $path)
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader([$root])->find($path));
    }

    /**
     * @return string[][]
     */
    public static function provideMultipleRootLoadCases()
    {
        $pathsPrepended = [
            realpath(__DIR__.'/../'),
            realpath(__DIR__.'/../../'),
            realpath(__DIR__.'/../../../'),
        ];

        return array_map(function ($parameters) use ($pathsPrepended) {
            return [[$pathsPrepended[mt_rand(0, \count($pathsPrepended) - 1)], $parameters[0]], $parameters[1]];
        }, static::provideLoadCases());
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     *
     * @param string[] $roots
     * @param string   $path
     */
    public function testMultipleRootLoadCases($roots, $path)
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader($roots)->find($path));
    }

    public function testAllowsEmptyRootPath()
    {
        $loader = $this->getFileSystemLoader([]);

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    public function testThrowsIfRootPathDoesNotExist()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $loader = $this->getFileSystemLoader(['/a/bad/root/path']);

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
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
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('Source image invalid');

        $loader = $this->getFileSystemLoader()->find($path);

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    public function testPathWithDoublePeriodBackStep()
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader()->find('/../../Binary/Loader/'.pathinfo(__FILE__, PATHINFO_BASENAME)));
    }

    public function testThrowsIfFileDoesNotExist()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable');

        $loader = $this->getFileSystemLoader()->find('fileNotExist');

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    /**
     * @param string[] $roots
     *
     * @return FileSystemLocator
     */
    private function getFileSystemLocator(array $roots)
    {
        return new FileSystemLocator($roots);
    }

    /**
     * @return string[]
     */
    private function getDefaultDataRoots()
    {
        return [__DIR__];
    }

    /**
     * @param array                 $roots
     * @param LocatorInterface|null $locator
     *
     * @return FileSystemLoader
     */
    private function getFileSystemLoader(array $roots = [], LocatorInterface $locator = null)
    {
        if (interface_exists(MimeTypeGuesserInterface::class)) {
            $mimeTypes = MimeTypes::getDefault();

            return new FileSystemLoader(
                $mimeTypes,
                $mimeTypes,
                null !== $locator ? $locator : $this->getFileSystemLocator(\count($roots) ? $roots : $this->getDefaultDataRoots())
            );
        }

        return new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            null !== $locator ? $locator : $this->getFileSystemLocator(\count($roots) ? $roots : $this->getDefaultDataRoots())
        );
    }

    /**
     * @param FileBinary|mixed $return
     * @param string|null      $message
     */
    private function assertValidLoaderFindReturn($return, string $message = ''): void
    {
        $this->assertInstanceOf(FileBinary::class, $return, $message);
        $this->assertStringStartsWith('text/', $return->getMimeType(), $message);
    }
}

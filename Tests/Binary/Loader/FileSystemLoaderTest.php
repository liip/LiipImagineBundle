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

        return array(
            array(
                __DIR__,
                $file,
            ),
            array(
                __DIR__.'/',
                $file,
            ),
            array(
                __DIR__, '/'.
                $file,
            ),
            array(
                __DIR__.'/../../Binary/Loader',
                '/'.$file,
            ),
            array(
                realpath(__DIR__.'/..'),
                'Loader/'.$file,
            ),
            array(
                __DIR__.'/../',
                '/Loader/../../Binary/Loader/'.$file,
            ),
        );
    }

    /**
     * @dataProvider provideLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testLoad($root, $path)
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader(array($root))->find($path));
    }

    /**
     * @return array[]
     */
    public static function provideMultipleRootLoadCases()
    {
        $pathsPrepended = array(
            realpath(__DIR__.'/../'),
            realpath(__DIR__.'/../../'),
            realpath(__DIR__.'/../../../'),
        );

        return array_map(function ($parameters) use ($pathsPrepended) {
            return array(array($pathsPrepended[mt_rand(0, count($pathsPrepended) - 1)], $parameters[0]), $parameters[1]);
        }, static::provideLoadCases());
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testMultipleRootLoadCases($root, $path)
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader($root)->find($path));
    }

    public function testAllowsEmptyRootPath()
    {
        $loader = $this->getFileSystemLoader(array());

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Root image path not resolvable
     */
    public function testThrowsIfRootPathDoesNotExist()
    {
        $loader = $this->getFileSystemLoader(array('/a/bad/root/path'));

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    /**
     * @return array[]
     */
    public function provideOutsideRootPathsData()
    {
        return array(
            array('../Loader/../../Binary/Loader/../../../Resources/config/routing.yaml'),
            array('../../Binary/'),
        );
    }

    /**
     * @dataProvider provideOutsideRootPathsData
     *
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessage Source image invalid
     *
     * @param string $path
     */
    public function testThrowsIfRealPathOutsideRootPath($path)
    {
        $loader = $this->getFileSystemLoader()->find($path);

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    public function testPathWithDoublePeriodBackStep()
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader()->find('/../../Binary/Loader/'.pathinfo(__FILE__, PATHINFO_BASENAME)));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessage Source image not resolvable
     */
    public function testThrowsIfFileDoesNotExist()
    {
        $loader = $this->getFileSystemLoader()->find('fileNotExist');

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    /**
     * @return FileSystemLocator
     */
    private function getFileSystemLocator()
    {
        return new FileSystemLocator();
    }

    /**
     * @return string[]
     */
    private function getDefaultDataRoots()
    {
        return array(__DIR__);
    }

    /**
     * @param string|array|null     $root
     * @param LocatorInterface|null $locator
     *
     * @return FileSystemLoader
     */
    private function getFileSystemLoader($root = null, LocatorInterface $locator = null)
    {
        return new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            null !== $locator ? $locator : $this->getFileSystemLocator(),
            null !== $root ? $root : $this->getDefaultDataRoots()
        );
    }

    /**
     * @param FileBinary|mixed $return
     * @param string|null      $message
     */
    private function assertValidLoaderFindReturn($return, $message = null)
    {
        $this->assertInstanceOf(FileBinary::class, $return, $message);
        $this->assertStringStartsWith('text/', $return->getMimeType(), $message);
    }
}

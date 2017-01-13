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
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\FileSystemLoader
 */
class FileSystemLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementLoaderInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Binary\Loader\FileSystemLoader');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Binary\Loader\LoaderInterface'));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );
    }

    public function testThrowExceptionIfNoRootPathsProvided()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\InvalidArgumentException',
            'One or more data root paths must be specified.'
        );

        new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array()
        );
    }

    public function testThrowExceptionIfRootPathIsEmpty()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\InvalidArgumentException',
            'Root image path not resolvable'
        );

        new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            ''
        );
    }

    public function testThrowExceptionIfRootPathDoesNotExist()
    {
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\InvalidArgumentException',
            'Root image path not resolvable'
        );

        new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            '/a/bad/root/path'
        );
    }

    public function testThrowExceptionIfRealPathIsOutsideRootPath1()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image invalid'
        );

        $loader->find('../Loader/../../Binary/Loader/../../../Resources/config/routing.xml');
    }

    public function testThrowExceptionIfRealPathIsOutsideRootPath2()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image invalid'
        );

        $loader->find('../../Binary/');
    }

    public function testThrowExceptionIfPathHasDoublePointSlashInTheMiddle()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $loader->find('/../../Binary/Loader/'.pathinfo(__FILE__, PATHINFO_BASENAME));
    }

    public function testThrowExceptionIfFileNotExist()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image not resolvable'
        );

        $loader->find('fileNotExist');
    }

    public static function provideLoadCases()
    {
        $fileName = pathinfo(__FILE__, PATHINFO_BASENAME);

        return array(
            array(__DIR__, $fileName),
            array(__DIR__.'/', $fileName),
            array(__DIR__, '/'.$fileName),
            array(__DIR__.'/../../Binary/Loader', '/'.$fileName),
            array(realpath(__DIR__.'/..'), 'Loader/'.$fileName),
            array(__DIR__.'/../', '/Loader/../../Binary/Loader/'.$fileName),
        );
    }

    /**
     * @dataProvider provideLoadCases
     */
    public function testLoad($rootDir, $path)
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            $rootDir
        );

        $binary = $loader->find($path);

        $this->assertInstanceOf('Liip\ImagineBundle\Model\FileBinary', $binary);
        $this->assertStringStartsWith('text/', $binary->getMimeType());
    }

    public static function provideMultipleRootLoadCases()
    {
        $prepend = array(
            realpath(__DIR__.'/../'),
            realpath(__DIR__.'/../../'),
            realpath(__DIR__.'/../../../'),
        );

        return array_map(function ($params) use ($prepend) {
            return array(array($prepend[mt_rand(0, count($prepend) - 1)], $params[0]), $params[1]);
        }, static::provideLoadCases());
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     */
    public function testMultipleRootLoadCases($rootDirs, $path)
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            $rootDirs
        );

        $binary = $loader->find($path);

        $this->assertInstanceOf('Liip\ImagineBundle\Model\FileBinary', $binary);
        $this->assertStringStartsWith('text/', $binary->getMimeType());
    }
}

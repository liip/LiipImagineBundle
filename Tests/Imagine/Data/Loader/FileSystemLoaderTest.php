<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

class FileSystemLoaderTest extends \PHPUnit_Framework_TestCase
{
    public static function provideLoadCases()
    {
        $fileName = pathinfo(__FILE__, PATHINFO_BASENAME);
var_dump(array(__DIR__, $fileName));
        return array(
            array(__DIR__, $fileName),
            array(__DIR__.'/', $fileName),
            array(__DIR__, '/'.$fileName),
            array(__DIR__.'/', '/'.$fileName),
            array(realpath(__DIR__.'/../..'), 'Data/Loader/'.$fileName),
            array(realpath(__DIR__.'/../../'), '/Data/Loader/'.$fileName),
        );
    }

    public function testShouldImplementLoaderInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface'));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );
    }

    public function testThrowExceptionIfPathHasDoublePointSlashAtBegging()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
            'Source image was searched with'
        );

        $loader->find('../foo/bar');
    }

    public function testThrowExceptionIfPathHasDoublePointSlashInTheMiddle()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
            'Source image was searched with'
        );

        $loader->find('foo/../bar');
    }

    public function testThrowExceptionIfFileNotExist()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
            'Source image not found'
        );

        $loader->find('fileNotExist');
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

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals('text/x-php', $binary->getMimeType());
    }
}

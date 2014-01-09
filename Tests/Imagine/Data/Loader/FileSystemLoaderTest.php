<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader
 */
class FileSystemLoaderTest extends AbstractTest
{
    /**
     * @dataProvider invalidPathProvider
     */
    public function testFindInvalidPath($path)
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array(),
            $this->fixturesDir.'/assets'
        );

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $loader->find($path);
    }

    public function testFindNotExisting()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array('jpeg'),
            $this->tempDir
        );

        $file = realpath($this->tempDir).'/invalid.jpeg';
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'Source image not found in "'.$file.'"');

        $loader->find('/invalid.jpeg');
    }

    public function testFindWithNoExtensionDoesNotThrowNotice()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array(),
            $this->tempDir
        );

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $loader->find('/invalid');
    }

    public function testFindRetrievesImage()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/cats.jpeg');

        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array('jpeg'),
            $this->fixturesDir.'/assets'
        );

        $binary = $loader->find('/cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals($expectedContent, $binary->getContent());
        $this->assertEquals('image/jpeg', $binary->getMimeType());
    }

    public function testFindWithCyrillicFilename()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/АГГЗ.jpeg');

        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array('jpeg'),
            $this->fixturesDir.'/assets'
        );

        $binary = $loader->find('/АГГЗ.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals($expectedContent, $binary->getContent());
        $this->assertEquals('image/jpeg', $binary->getMimeType());
    }

    public function testFindGuessesFormat()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/cats.jpeg');

        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array('jpeg'),
            $this->fixturesDir.'/assets'
        );

        $binary = $loader->find('/cats.jpg');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals($expectedContent, $binary->getContent());
        $this->assertEquals('image/jpeg', $binary->getMimeType());
    }

    public function testFindFileWithoutExtension()
    {
        $this->filesystem->copy($this->fixturesDir.'/assets/cats.jpeg', $this->tempDir.'/cats');

        $expectedContent = file_get_contents(realpath($this->tempDir.'/cats'));

        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array(),
            $this->tempDir
        );

        $binary = $loader->find('/cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Model\Binary', $binary);
        $this->assertEquals($expectedContent, $binary->getContent());
        $this->assertEquals('image/jpeg', $binary->getMimeType());
    }
}

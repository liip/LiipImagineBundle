<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

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
        $loader = new FileSystemLoader(array(), $this->fixturesDir.'/assets');

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $loader->find($path);
    }

    public function testFindNotExisting()
    {
        $loader = new FileSystemLoader(array('jpeg'), $this->tempDir);

        $file = realpath($this->tempDir).'/invalid.jpeg';
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'Source image not found in "'.$file.'"');

        $loader->find('/invalid.jpeg');
    }

    public function testFindWithNoExtensionDoesNotThrowNotice()
    {
        $loader = new FileSystemLoader(array(), $this->tempDir);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $loader->find('/invalid');
    }

    public function testFindRetrievesImage()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/cats.jpeg');

        $loader = new FileSystemLoader(array('jpeg'), $this->fixturesDir.'/assets');

        $rawImage = $loader->find('/cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\RawImage', $rawImage);
        $this->assertEquals($expectedContent, $rawImage->getContent());
        $this->assertEquals('image/jpeg', $rawImage->getMimeType());
    }

    public function testFindWithCyrillicFilename()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/АГГЗ.jpeg');

        $loader = new FileSystemLoader(array('jpeg'), $this->fixturesDir.'/assets');

        $rawImage = $loader->find('/АГГЗ.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\RawImage', $rawImage);
        $this->assertEquals($expectedContent, $rawImage->getContent());
        $this->assertEquals('image/jpeg', $rawImage->getMimeType());
    }

    public function testFindGuessesFormat()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/cats.jpeg');

        $loader = new FileSystemLoader(array('jpeg'), $this->fixturesDir.'/assets');

        $rawImage = $loader->find('/cats.jpg');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\RawImage', $rawImage);
        $this->assertEquals($expectedContent, $rawImage->getContent());
        $this->assertEquals('image/jpeg', $rawImage->getMimeType());
    }

    public function testFindFileWithoutExtension()
    {
        $this->filesystem->copy($this->fixturesDir.'/assets/cats.jpeg', $this->tempDir.'/cats');

        $expectedContent = file_get_contents(realpath($this->tempDir.'/cats'));

        $loader = new FileSystemLoader(array(), $this->tempDir);

        $rawImage = $loader->find('/cats.jpeg');

        $this->assertInstanceOf('Liip\ImagineBundle\Imagine\RawImage', $rawImage);
        $this->assertEquals($expectedContent, $rawImage->getContent());
        $this->assertEquals('image/jpeg', $rawImage->getMimeType());
    }
}

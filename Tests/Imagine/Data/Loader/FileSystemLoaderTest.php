<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader
 */
class FileSystemLoaderTest extends AbstractTest
{
    protected $imagine;

    protected function setUp()
    {
        parent::setUp();

        $this->imagine = $this->getMockImagine();
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testFindInvalidPath($path)
    {
        $loader = new FileSystemLoader($this->imagine, array(), $this->fixturesDir.'/assets');

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $loader->find($path);
    }

    public function testFindNotExisting()
    {
        $this->imagine
            ->expects($this->never())
            ->method('open')
        ;

        $loader = new FileSystemLoader($this->imagine, array('jpeg'), $this->tempDir);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $loader->find('/invalid.jpeg');
    }

    public function testFindWithNoExtensionDoesNotThrowNotice()
    {
        $loader = new FileSystemLoader($this->imagine, array(), $this->tempDir);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $loader->find('/invalid');
    }

    public function testFindRetrievesImage()
    {
        $image = $this->getMockImage();

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with(realpath($this->fixturesDir.'/assets/cats.jpeg'))
            ->will($this->returnValue($image))
        ;

        $loader = new FileSystemLoader($this->imagine, array('jpeg'), $this->fixturesDir.'/assets');
        $this->assertSame($image, $loader->find('/cats.jpeg'));
    }

    public function testFindGuessesFormat()
    {
        $image = $this->getMockImage();

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with(realpath($this->fixturesDir.'/assets/cats.jpeg'))
            ->will($this->returnValue($image))
        ;

        $loader = new FileSystemLoader($this->imagine, array('jpeg'), $this->fixturesDir.'/assets');
        $this->assertSame($image, $loader->find('/cats.jpg'));
    }

    public function testFindFileWithoutExtension()
    {
        $image = $this->getMockImage();

        $this->filesystem->copy($this->fixturesDir.'/assets/cats.jpeg', $this->tempDir.'/cats');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with(realpath($this->tempDir.'/cats'))
            ->will($this->returnValue($image))
        ;

        $loader = new FileSystemLoader($this->imagine, array(), $this->tempDir);
        $this->assertSame($image, $loader->find('/cats.jpeg'));
    }
}

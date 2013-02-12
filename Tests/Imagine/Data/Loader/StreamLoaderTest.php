<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\Data\Loader\StreamLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Data\Loader\StreamLoader
 */
class StreamLoaderTest extends AbstractTest
{
    protected $imagine;

    protected function setUp()
    {
        parent::setUp();

        $this->imagine = $this->getMockImagine();
    }

    public function testFindInvalidFile()
    {
        $this->imagine
            ->expects($this->never())
            ->method('load')
        ;

        $loader = new StreamLoader($this->imagine, 'file://');

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $loader->find($this->tempDir.'/invalid.jpeg');
    }

    public function testFindLoadsFile()
    {
        $image = $this->getMockImage();

        $this->imagine
            ->expects($this->once())
            ->method('load')
            ->with(file_get_contents($this->fixturesDir.'/assets/cats.jpeg'))
            ->will($this->returnValue($image))
        ;

        $loader = new StreamLoader($this->imagine, 'file://');
        $this->assertSame($image, $loader->find($this->fixturesDir.'/assets/cats.jpeg'));
    }

    public function testFindWithContext()
    {
        $image = $this->getMockImage();

        $this->imagine
            ->expects($this->once())
            ->method('load')
            ->with(file_get_contents($this->fixturesDir.'/assets/cats.jpeg'))
            ->will($this->returnValue($image))
        ;

        $context = stream_context_create();

        $loader = new StreamLoader($this->imagine, 'file://', $context);
        $this->assertSame($image, $loader->find($this->fixturesDir.'/assets/cats.jpeg'));
    }

    public function testConstructorInvalidContext()
    {
        $this->setExpectedException('InvalidArgumentException', 'The given context is no valid resource.');

        new StreamLoader($this->imagine, 'file://', true);
    }
}
